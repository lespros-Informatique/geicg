/**
 * Module JS : Tableau de Bord Principal & Graphiques ApexCharts (views/home/index.php)
 */
$(document).ready(function() {
  var configEl = document.getElementById('home-dashboard-config');
  var config = {};
  if (configEl) {
    try { config = JSON.parse(configEl.textContent); } catch (e) {}
  }

  if (window.lucide) {
    lucide.createIcons();
  }

  // --- Graphique 1 : Flux Financier Mensuel (Encaissements vs Dépenses) ---
  var financialContainer = document.querySelector("#chart-financial-flux");
  if (financialContainer && typeof ApexCharts !== 'undefined') {
    var monthlyData = config.monthlyFinancials || {};
    
    var optionsFlux = {
      series: [
        {
          name: 'Encaissements Caisse',
          data: monthlyData.encaissements || [0,0,0,0,0,0,0,0,0,0,0,0]
        },
        {
          name: 'Dépenses Engagées',
          data: monthlyData.depenses || [0,0,0,0,0,0,0,0,0,0,0,0]
        }
      ],
      chart: {
        type: 'area',
        height: 300,
        fontFamily: 'Inter, sans-serif',
        toolbar: { show: false }
      },
      colors: ['#047857', '#DC2626'],
      dataLabels: { enabled: false },
      stroke: { curve: 'smooth', width: 3 },
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.45,
          opacityTo: 0.05,
          stops: [0, 90, 100]
        }
      },
      xaxis: {
        categories: monthlyData.labels || ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
        axisBorder: { show: false },
        axisTicks: { show: false }
      },
      yaxis: {
        labels: {
          formatter: function(val) {
            return (val >= 1000) ? (val / 1000).toFixed(0) + 'k' : val;
          }
        }
      },
      tooltip: {
        y: {
          formatter: function(val) {
            return new Intl.NumberFormat('fr-FR').format(val) + ' FCFA';
          }
        }
      },
      legend: { position: 'top', horizontalAlign: 'right' }
    };

    var chartFlux = new ApexCharts(financialContainer, optionsFlux);
    chartFlux.render();
  }

  // --- Graphique 2 : Répartition par Filières (Donut Chart) ---
  var filiereContainer = document.querySelector("#chart-filieres-dist");
  if (filiereContainer && typeof ApexCharts !== 'undefined') {
    var filieresData = config.filieresDistribution || {};

    var optionsFiliere = {
      series: filieresData.series || [],
      labels: filieresData.labels || [],
      chart: {
        type: 'donut',
        height: 300,
        fontFamily: 'Inter, sans-serif'
      },
      colors: ['#1E3A5F', '#047857', '#7C3AED', '#D97706', '#0284C7', '#DC2626', '#475569'],
      legend: { position: 'bottom' },
      dataLabels: { enabled: true },
      responsive: [{
        breakpoint: 480,
        options: {
          chart: { width: 300 },
          legend: { position: 'bottom' }
        }
      }]
    };

    var chartFiliere = new ApexCharts(filiereContainer, optionsFiliere);
    chartFiliere.render();
  }
});

