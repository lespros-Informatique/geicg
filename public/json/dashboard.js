$(document).ready(function() {
    function loadDashboard() {
        $.getJSON(LINK + 'home/dashboardData', function(resp) {
            const s = resp.stats || {};
            const commandes = s.commandes || {};

            const commandesActives = commandes['actif'] ?? 0;
            const commandesAnnulees = commandes['annule'] ?? 0;

            $('[data-stat="users"]').text(s.users ?? 0);
            $('[data-stat="clients"]').text(s.clients ?? 0);
            $('[data-stat="articles"]').text(s.articles ?? 0);
            $('[data-stat="commandes"]').text(s.commandes ?? 0);

            if (resp.recentOrders && resp.recentOrders.length) {
                const tbody = $('#recentOrdersTable tbody');
                tbody.empty();
                const headers = ['Code', 'Client', 'Montant', 'Statut', 'Date'];
                resp.recentOrders.forEach(function(o) {
                    const tr = $('<tr>' +
                        '<td>' + (o.code_commande || '') + '</td>' +
                        '<td>' + (o.nom_client || '') + '</td>' +
                        '<td>' + Number(o.montant_total_commande || 0).toLocaleString('fr-FR') + ' FCFA</td>' +
                        '<td><span class="badge-status ' + (o.statut_commande || '') + '">' + (o.statutLabel || '') + '</span></td>' +
                        '<td>' + (o.created_at_commande || '') + '</td>' +
                    '</tr>');
                    tr.find('td').each(function(idx, td) {
                        $(td).attr('data-label', headers[idx] || '');
                    });
                    tbody.append(tr);
                });

                if (window.innerWidth <= 768) {
                    var mobileContainer = $('#recentOrdersMobile');
                    var mobileHtml = '';
                    resp.recentOrders.forEach(function(o) {
                        mobileHtml += '<div class="mobile-item">' +
                            '<div class="mobile-item-body">' +
                                '<div class="mobile-item-primary">' +
                                    '<span class="mobile-item-label">Commande</span>' +
                                    '<span class="mobile-item-value">' + (o.code_commande || '') + '</span>' +
                                '</div>' +
                                '<div class="mobile-item-primary">' +
                                    '<span class="mobile-item-label">Client</span>' +
                                    '<span class="mobile-item-value">' + (o.nom_client || '') + '</span>' +
                                '</div>' +
                                '<div class="mobile-item-meta">' +
                                    '<small>Montant: ' + Number(o.montant_total_commande || 0).toLocaleString('fr-FR') + ' FCFA</small>' +
                                    '<small>Statut: <span class="badge-status ' + (o.statut_commande || '') + '">' + (o.statutLabel || '') + '</span></small>' +
                                    '<small>Date: ' + (o.created_at_commande || '') + '</small>' +
                                '</div>' +
                            '</div>' +
                            '<a href="' + LINK + 'commande/details/' + o.editId + '" class="mobile-actions-toggle" aria-label="Voir">' +
                                '<i data-lucide="chevron-right" style="width:18px;height:18px;"></i>' +
                            '</a>' +
                        '</div>';
                    });
                    mobileContainer.html(mobileHtml);
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                } else {
                    $('#recentOrdersMobile').empty();
                }
            }
        });
    }

    loadDashboard();
});
