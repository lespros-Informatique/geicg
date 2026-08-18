$(document).ready(function() {
    const baseApi = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : '/admin-lavex/');

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
                title: 'Libellé Forfait',
                render: function(data) {
                    return '<strong style="color: #1E293B;">' + (data || '') + '</strong>';
                }
            },
            { 
                data: 'montant', 
                title: 'Montant',
                render: function(data) {
                    const val = Number(data || 0).toLocaleString('fr-FR');
                    return '<span style="font-weight: 800; color: #059669;">' + val + ' FCFA</span>';
                }
            },
            { 
                data: 'duree_mois', 
                title: 'Durée',
                render: function(data) {
                    const mois = parseInt(data || 1);
                    return '<span style="color: #64748B; font-weight: 600;">' + mois + ' mois</span>';
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
                            <a href="${baseApi}forfait/edition/${row.editId}" class="btn-action btn-action-primary" title="Modifier le forfait">
                                <i class="fa fa-edit"></i>
                            </a>
                            <button type="button" class="btn-action ${isActif ? 'btn-action-warning' : 'btn-action-success'} btnToggleForfaitStatut"
                                    data-id="${row.id}" title="${isActif ? 'Désactiver' : 'Activer'}">
                                <i class="fa ${isActif ? 'fa-pause' : 'fa-play'}"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ];

        const table = initDataTable('dataTable', 'forfait/apiList', columns);

        // Toggle Status Forfait
        $(document).on('click', '.btnToggleForfaitStatut', function() {
            const id = $(this).data('id');
            if (!id) return;

            $.post(baseApi + 'forfait/changer', { id: id }, function(rep) {
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
        const forfaitsMobileConfig = {
            entity: 'forfait',
            primary: [{ key: 'code', label: 'Code' }, { key: 'libelle', label: 'Libellé' }],
            secondary: [{ key: 'montant', label: 'Montant' }, { key: 'duree_mois', label: 'Durée (mois)' }],
            actions: [
                { id: 'modifier', label: 'Modifier', icon: 'edit', href: function(r) { return baseApi + 'forfait/edition/' + r.editId; } }
            ]
        };
        renderMobileCards('dataTable', forfaitsMobileConfig);
    }

    // Formulaire d'ajout de forfait
    $('#formAddForfait').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('.btn_actions');

        if (typeof loading === 'function') loading(btn, true, 'Enregistrement...');

        $.ajax({
            url: baseApi + 'forfait/add',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(rep) {
                if (typeof loading === 'function') loading(btn, false);
                if (rep.status) {
                    if (typeof showToast === 'function') showToast(rep.message || 'Forfait créé avec succès !', 'success');
                    closeAddForfaitModal();
                    form[0].reset();
                    if ($('#dataTable').length) {
                        $('#dataTable').DataTable().ajax.reload(null, false);
                    }
                } else {
                    if (typeof showToast === 'function') showToast(rep.message || 'Erreur lors de la création', 'error');
                }
            },
            error: function(xhr) {
                if (typeof loading === 'function') loading(btn, false);
                let msg = 'Erreur lors de la création';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                if (typeof showToast === 'function') showToast(msg, 'error');
            }
        });
    });

    // Formulaire d'édition de forfait
    $('#formEditForfait').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('.btn_actions');

        if (typeof loading === 'function') loading(btn, true, 'Sauvegarde...');

        $.ajax({
            url: baseApi + 'forfait/edit',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(rep) {
                if (typeof loading === 'function') loading(btn, false);
                if (rep.status) {
                    if (typeof showToast === 'function') showToast(rep.message || 'Forfait modifié avec succès !', 'success');
                    setTimeout(function() {
                        window.location.href = baseApi + 'forfait/list';
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

function openAddForfaitModal() {
    $('#modalAddForfait').css('display', 'flex');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeAddForfaitModal() {
    $('#modalAddForfait').hide();
}
