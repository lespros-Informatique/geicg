$(document).ready(function() {
    if ($('#dataTable').length) {
        const columns = [
            { title: 'N°', data: null, render: function(data, type, row, meta) { return meta.row + 1; } },
            {
                data: 'icon',
                title: 'Icône',
                className: 'text-center',
                render: function(data, type, row) {
                    if (data && data.trim() !== '') {
                        let iconSrc = '';
                        if (data.startsWith('http') || data.startsWith('data:')) {
                            iconSrc = data;
                        } else if (data.includes('/')) {
                            iconSrc = LINK + data.replace(/^\/+/, '');
                        } else if (data.endsWith('.svg') || data.endsWith('.png') || data.endsWith('.jpg') || data.endsWith('.webp')) {
                            iconSrc = LINK + 'assets/images/categories/' + data;
                        } else {
                            return `<div style="width: 38px; height: 38px; border-radius: 8px; background: #EFF6FF; color: #2563EB; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; margin: auto;"><i class="fa fa-${data}"></i></div>`;
                        }
                        return `
                            <div style="width: 38px; height: 38px; border-radius: 8px; background: #F8FAFC; border: 1px solid #E2E8F0; display: inline-flex; align-items: center; justify-content: center; overflow: hidden; margin: auto;">
                                <img src="${iconSrc}" alt="${row.libelle || ''}" onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\\'fa fa-folder\\' style=\\'color:#94A3B8; font-size:16px;\\'></i>';" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        `;
                    }
                    return `<div style="width: 38px; height: 38px; border-radius: 8px; background: #F1F5F9; color: #94A3B8; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; margin: auto;"><i class="fa fa-folder"></i></div>`;
                }
            },
            { data: 'code', title: 'Code' },
            {
                data: 'libelle',
                title: 'Libellé',
                render: function(data, type, row) {
                    return `<strong>${data || '-'}</strong>`;
                }
            },
            { data: 'description', title: 'Description' },
            {
                data: 'statut',
                title: 'Statut',
                render: function(data) {
                    const cls = data === 'actif' ? 'delivered' : 'cancelled';
                    return '<span class="badge-status ' + cls + '">' + data + '</span>';
                }
            },
            {
                data: null,
                title: 'Actions',
                className: 'text-center',
                render: function(data, type, row) {
                    const isActive = row.statut === 'actif';
                    return `
                        <div class="table-actions" style="display: flex; justify-content: center; gap: 6px; flex-wrap: wrap;">
                            <a href="${LINK}categorie/details/${row.editId}" title="Voir" class="btn-action btn-action-secondary"><i class="fa fa-eye"></i></a>
                            <a href="${LINK}categorie/edition/${row.editId}" title="Modifier" class="btn-action btn-action-primary"><i class="fa fa-edit"></i></a>
                            <button type="button" title="${isActive ? 'Désactiver' : 'Activer'}"
                                data-id="${row.id}"
                                class="${isActive ? 'btn-action btn-action-warning changerStatus' : 'btn-action btn-action-success changerStatus'}">
                                <i class="fa ${isActive ? 'fa-toggle-on' : 'fa-toggle-off'}"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ];

        const table = initDataTable('dataTable', 'categorie/apiList', columns);
        bindStatusToggle('dataTable', 'categorie/changer', table);

        const categoriesMobileConfig = {
            entity: 'categorie',
            primary: [{ key: 'libelle', label: 'Catégorie' }],
            secondary: [{ key: 'code', label: 'Code' }, { key: 'description', label: 'Description' }],
            detailUrl: function(r) { return LINK + 'categorie/details/' + r.editId; },
            actions: [
                { id: 'voir', label: 'Voir', icon: 'eye', href: function(r) { return LINK + 'categorie/details/' + r.editId; } },
                { id: 'modifier', label: 'Modifier', icon: 'edit', href: function(r) { return LINK + 'categorie/edition/' + r.editId; } },
            ],
            getActions: function(row) {
                var list = categoriesMobileConfig.actions.map(function(a) { return Object.assign({}, a, { href: a.href(row) }); });
                var isActive = row.statut === 'actif';
                list.push({
                    id: isActive ? 'desactiver' : 'activer',
                    label: isActive ? 'Désactiver' : 'Activer',
                    icon: isActive ? 'toggle-left' : 'toggle-right',
                    onClick: function(rowData) {
                        showConfirm('Changer le statut de cette catégorie ?', function() {
                            $.post(LINK + 'categorie/changer', { id: rowData.id }, function(rep) {
                                showToast(rep.message || 'Statut changé', rep.status ? 'success' : 'error');
                                $('#dataTable').DataTable().ajax.reload();
                            }, 'json').fail(function() { showToast('Erreur serveur', 'error'); });
                        });
                    }
                });
                return list;
            }
        };
        renderMobileCards('dataTable', categoriesMobileConfig);
    }

    $('.formEditCategorie').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('.btn_actions');
        const idCategorie = form.find('#id_categorie_article').val();
        const isAdd = !idCategorie;
        const url = isAdd ? LINK + 'categorie/add' : LINK + 'categorie/edit';
        const formData = new FormData(this);

        loading(btn, true, '<i class="fa fa-spinner fa-spin"></i> Enregistrement...');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(rep) {
                loading(btn, false, '<i class="fa fa-save"></i> Sauvegarder');
                if (rep.status) {
                    showToast(rep.message, 'success');
                    setTimeout(() => window.location.href = LINK + 'categorie/list', 700);
                } else {
                    showToast(rep.message, 'error');
                }
            },
            error: function(xhr) {
                loading(btn, false, '<i class="fa fa-save"></i> Sauvegarder');
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Erreur serveur';
                showToast(msg, 'error');
            }
        });
    });
});
