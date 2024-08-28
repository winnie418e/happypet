$(document).ready(function () {
  flatpickr("#checkin, #checkout", {
    locale: {
      firstDayOfWeek: 1, // 設置每週的第一天為週一
      weekdays: {
        shorthand: ["日", "一", "二", "三", "四", "五", "六"],
        longhand: [
          "星期日",
          "星期一",
          "星期二",
          "星期三",
          "星期四",
          "星期五",
          "星期六",
        ],
      },
      months: {
        shorthand: [
          "1月",
          "2月",
          "3月",
          "4月",
          "5月",
          "6月",
          "7月",
          "8月",
          "9月",
          "10月",
          "11月",
          "12月",
        ],
        longhand: [
          "一月",
          "二月",
          "三月",
          "四月",
          "五月",
          "六月",
          "七月",
          "八月",
          "九月",
          "十月",
          "十一月",
          "十二月",
        ],
      },
    },
    showMonths: 1, // 確保只顯示一個月的日期選擇器
  });
});
