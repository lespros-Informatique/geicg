$(document).ready(function() {
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
                    return '<strong style="color: #059669; font-size: 14px;">' + Number(data || 0).toLocaleString('fr-FR') + ' FCFA</strong>';
                }
            },
            { 
                data: 'duree_mois', 
                title: 'Durée',
                render: function(data) {
                    return '<span style="font-weight: 600; color: #475569;">' + (data || 1) + ' mois</span>';
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
                    const baseApi = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : '/admin-lavex/');
                    const isActif = (row.statut === 'actif');
                    return `
                        <div class="table-actions">
                            <a href="${baseApi}forfait/edition/${row.editId}" class="btn-action btn-action-primary" title="Modifier">
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

        $(document).on('click', '.btnToggleForfaitStatut', function() {
            const id = $(this).data('id');
            const baseApi = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : '/admin-lavex/');
            if (confirm('Voulez-vous modifier le statut de ce forfait ?')) {
                $.post(baseApi + 'forfait/changer', { id: id }, function(rep) {
                    if (typeof showToast === 'function') {
                        showToast(rep.message || 'Statut mis à jour', rep.status ? 'success' : 'error');
                    }
                    table.ajax.reload();
                }, 'json').fail(function() {
                    if (typeof showToast === 'function') showToast('Erreur serveur', 'error');
                });
            }
        });

        const forfaitsMobileConfig = {
            entity: 'forfait',
            primary: [{ key: 'libelle', label: 'Forfait' }],
            secondary: [{ key: 'code', label: 'Code' }, { key: 'montant', label: 'Montant' }, { key: 'statut', label: 'Statut' }],
            detailUrl: function(r) { return (typeof LINK !== 'undefined' ? LINK : '/admin-lavex/') + 'forfait/edition/' + r.editId; },
            actions: [
                {
                    label: 'Modifier',
                    url: function(r) { return (typeof LINK !== 'undefined' ? LINK : '/admin-lavex/') + 'forfait/edition/' + r.editId; },
                    icon: 'edit'
                }
            ]
        };
        renderMobileCards('dataTable', forfaitsMobileConfig);
    }
});
