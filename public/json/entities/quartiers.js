$(document).ready(function() {
    const baseApi = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : '/geicg/');

    if ($('#dataTable').length) {
        const columns = [
            { 
                title: 'N°', 
                data: null, 
                render: function(data, type, row, meta) { return meta.row + 1; } 
            },
            { 
                data: 'code', 
                title: 'Code',
                render: function(data) {
                    return '<span class="code-badge">' + (data || '') + '</span>';
                }
            },
            { 
                data: 'libelle', 
                title: 'Quartier',
                render: function(data) {
                    return '<strong style="color: #1E293B;">' + (data || '') + '</strong>';
                }
            },
            { 
                data: 'ville_nom', 
                title: 'Ville',
                render: function(data) {
                    return '<span style="color: #2563EB; font-weight: 600;"><i class="fa fa-map-marker-alt"></i> ' + (data || '-') + '</span>';
                }
            },
            { 
                data: 'statut', 
                title: 'Statut',
                render: function(data) {
                    const isActif = (data === 'actif');
                    const cls = isActif ? 'delivered' : 'cancelled';
                    const label = isActif ? 'Actif' : 'Inactif';
                    return '<span class="badge-status ' + cls + '">' + label + '</span>';
                }
            },
            {
                data: null,
                title: 'Actions',
                className: 'text-center',
                render: function(data, type, row) {
                    const isActif = (row.statut === 'actif');
                    return `
                        <div class="table-actions">
                            <a href="${baseApi}quartier/edition/${row.editId}" class="btn-action btn-action-primary" title="Modifier le quartier">
                                <i class="fa fa-edit"></i>
                            </a>
                            <button type="button" class="btn-action ${isActif ? 'btn-action-warning' : 'btn-action-success'} btnToggleQuartierStatut"
                                    data-id="${row.id}" title="${isActif ? 'Désactiver' : 'Activer'}">
                                <i class="fa ${isActif ? 'fa-pause' : 'fa-play'}"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ];

        const table = initDataTable('dataTable', 'quartier/apiList', columns);

        // Toggle Status Quartier
        $(document).on('click', '.btnToggleQuartierStatut', function() {
            const id = $(this).data('id');
            if (!id) return;

            $.post(baseApi + 'quartier/changer', { id: id }, function(rep) {
                if (rep.status) {
                    if (typeof showToast === 'function') showToast(rep.message || 'Statut mis à jour', 'success');
                    table.ajax.reload(null, false);
                } else {
                    if (typeof showToast === 'function') showToast(rep.message || 'Erreur', 'error');
                }
            }, 'json').fail(function() {
                if (typeof showToast === 'function') showToast('Erreur serveur', 'error');
            });
        });

        // Mobile cards
        const quartiersMobileConfig = {
            entity: 'quartier',
            primary: [{ key: 'code', label: 'Code' }, { key: 'libelle', label: 'Quartier' }],
            secondary: [{ key: 'ville_nom', label: 'Ville' }, { key: 'statut', label: 'Statut' }],
            actions: [
                { id: 'modifier', label: 'Modifier', icon: 'edit', href: function(r) { return baseApi + 'quartier/edition/' + r.editId; } }
            ]
        };
        renderMobileCards('dataTable', quartiersMobileConfig);
    }

    // Formulaire d'ajout de quartier
    $('#formAddQuartier').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('.btn_actions');

        if (typeof loading === 'function') loading(btn, true, 'Enregistrement...');

        $.ajax({
            url: baseApi + 'quartier/add',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(rep) {
                if (typeof loading === 'function') loading(btn, false);
                if (rep.status) {
                    if (typeof showToast === 'function') showToast(rep.message || 'Quartier ajouté avec succès !', 'success');
                    closeAddQuartierModal();
                    form[0].reset();
                    if ($('#dataTable').length) {
                        $('#dataTable').DataTable().ajax.reload(null, false);
                    }
                } else {
                    if (typeof showToast === 'function') showToast(rep.message || 'Erreur lors de l\'ajout', 'error');
                }
            },
            error: function(xhr) {
                if (typeof loading === 'function') loading(btn, false);
                let msg = 'Erreur lors de l\'ajout';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                if (typeof showToast === 'function') showToast(msg, 'error');
            }
        });
    });

    // Formulaire d'édition de quartier
    $('#formEditQuartier').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('.btn_actions');

        if (typeof loading === 'function') loading(btn, true, 'Sauvegarde...');

        $.ajax({
            url: baseApi + 'quartier/edit',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(rep) {
                if (typeof loading === 'function') loading(btn, false);
                if (rep.status) {
                    if (typeof showToast === 'function') showToast(rep.message || 'Quartier modifié avec succès !', 'success');
                    setTimeout(function() {
                        window.location.href = baseApi + 'quartier/list';
                    }, 800);
                } else {
                    if (typeof showToast === 'function') showToast(rep.message || 'Erreur lors de la modification', 'error');
                }
            },
            error: function(xhr) {
                if (typeof loading === 'function') loading(btn, false);
                let msg = 'Erreur serveur';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                if (typeof showToast === 'function') showToast(msg, 'error');
            }
        });
    });
});

function openAddQuartierModal() {
    $('#modalAddQuartier').css('display', 'flex');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeAddQuartierModal() {
    $('#modalAddQuartier').hide();
}
