export const optionsSingle = {
  yAxis: {
    title: {
      text: null,
    },
  },
  tooltip: {
    enabled: false,
  },
}

export const optionsPie = {
  accessibility: {
    point: {
      valueSuffix: '%',
    },
  },
  plotOptions: {
    pie: {
      allowPointSelect: true,
      cursor: 'pointer',
      dataLabels: {
        enabled: true,
        format: '<b>{point.name}</b>: {point.percentage:.1f} %',
      },
    },
  },
}
