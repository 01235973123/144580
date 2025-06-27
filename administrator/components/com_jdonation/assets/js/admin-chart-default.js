(function (document, $, Joomla) {
    $(document).ready(function () {
		var currency_symbol = document.getElementById('currency_symbol').value;
        var ctx = document.getElementById('dms-sales-chart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Joomla.getOptions('labels'),
                datasets: [{
                    label: 'Received amount',
                    backgroundColor: 'rgb(255, 99, 132)',
                    borderColor: 'rgb(255, 99, 132)',
                    data: Joomla.getOptions('sales'),
                    fill: false,
                }]
            },
            plugins: [ChartDataLabels],
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function (value, index, values) {
                                return currency_symbol + value;
                            }
                        }
                    }]
                },
                plugins: {
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        font: {
                            weight: 'bold'
                        }
                    }
                }
            }
        });
    });
})(document, jQuery, Joomla);