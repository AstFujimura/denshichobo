$(document).ready(function() {
  $.ajax({
    url: '/drive/public/graph', // Laravelのルートに合わせて変更してください
    type: 'GET',
    dataType: 'json',
    success: function(response) {
      var labels = [];
      var values = [];
      
      // 取得したJSONデータからラベルと値を抽出します
      response.forEach(function(item) {
        labels.push(item.年月);
        values.push(item.走行距離);
      });
      
      // グラフの作成と描画
      createChart(labels, values);
    },
    error: function(xhr, status, error) {
      console.error(error);
    }
  });
});

function createChart(labels, values) {
  var ctx = document.getElementById('myChart').getContext('2d');
  var chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Distance',
        data: values,
        backgroundColor: 'rgba(0, 123, 255, 0.6)',
        borderColor: 'rgba(0, 123, 255, 1)',
        borderWidth: 1
      }]
    },
    options: {
      // responsive: true,
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
}
