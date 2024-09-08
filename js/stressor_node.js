document.addEventListener("DOMContentLoaded", function() {
    var parentDivs = document.querySelectorAll('.layout__region');

    parentDivs.forEach(function(parent) {
        if (parent.querySelector('.field--name-field-images')) {
            // If a grandchild div with the specific class is found, add a class to the parent
            parent.classList.add('two-column');
        }
    });
});



function createHighchartsFromTable() {
    var table = document.getElementById('field_stressor_response_csv_data-0-csvfiletable');
    if(!table) {
        return;
    }
    
    // X-lab
    var xlab = '';
    var parentDiv = document.querySelector('.field--name-field-stressor-name');
    var fieldItem = parentDiv ? parentDiv.querySelector('.field-item') : null;
    if (fieldItem) {
        var htmlContent = fieldItem.innerHTML;
        xlab = xlab.concat("[Stressor] ", htmlContent);
    } else {
        xlab = 'Raw Stressor Values';
    }
    var parentDiv = document.querySelector('.field--name-field-stressor-units');
    var fieldItem = parentDiv ? parentDiv.querySelector('.field-item') : null;
    if (fieldItem) {
        var htmlContent = fieldItem.innerHTML;
        xlab = xlab.concat(" ", htmlContent);
    }

    
    var rows = table.getElementsByTagName('tr');
    var categories = [];
    var data = [];
    var SD = [];
    var low_limit = [];
    var up_limit = [];
    var lim_array = [];

    for (var i = 1; i < rows.length; i++) {
        // Start at 1 to skip the header row
        var cells = rows[i].getElementsByTagName('td');
        if (cells.length > 1) {
            categories.push(cells[0].innerText);
            data.push(parseFloat(cells[1].innerText));
            SD.push(parseFloat(cells[2].innerText));
            low_limit.push(parseFloat(cells[3].innerText));
            up_limit.push(parseFloat(cells[4].innerText));

        }
    }

    // Need to add and subsctract SD from the data to get the low_limit and up_limit
    var data_SD_up = data.map(function (num, idx) {
        return num + SD[idx];
    });
    var data_SD_low = data.map(function (num, idx) {
        return num - SD[idx];
    }); 


    Highcharts.chart('chartContainer', {
        chart: {
            type: 'line' // Change chart type as needed (line, bar, etc.)
        },
        title: {
            text: '' // Setting title text to an empty string
        },
        xAxis: {
            categories: categories,
            title: {
                text: xlab
            }
        },
        yAxis: {
            title: {
                text: '[Response] Mean System Capacity (%)'
            }
        },
        series: [
        {
            name: '+1 SD',
            data: data_SD_up,
            color: '#FF5733',
            lineWidth: 1,
            dashStyle: 'LongDashDot',
            marker: {
                enabled: false
            }
        },
        {
            name: '-1 SD',
            data: data_SD_low,
            color: '#FF5733',
            lineWidth: 1,
            dashStyle: 'LongDashDot',
            marker: {
                enabled: false
            }
        },
        {
            name: 'low_limit',
            data: low_limit,
            color: '#989898',
            lineWidth: 2,
            dashStyle: 'Dash',
            marker: {
                enabled: false
            }
        },
        {
            name: 'up_limit',
            data: up_limit,
            color: '#989898',
            lineWidth: 2,
            dashStyle: 'Dash',
            marker: {
                enabled: false
            }
        },
        {
            name: 'Mean Response',
            data: data,
            color: '#0000FF', // Blue
            lineWidth: 4
        }
    ]
    });



    // Move chart to right after table
    var div1 = document.getElementById('field_stressor_response_csv_data-0-csvfiletable');
    var div2 = document.getElementById('parentChartContianer');
    
    // Move div2 to right after div1
    div1.insertAdjacentElement('afterend', div2);

}

// Call the function
createHighchartsFromTable();

