document.addEventListener("DOMContentLoaded", function () {
  // 初次加載時獲取所有訂單
  fetchOrders();

  // 當日期選擇發生變化時，根據選定的日期篩選訂單
  const checkinInput = document.getElementById("checkin");
  if (checkinInput) {
    checkinInput.addEventListener("change", function () {
      const checkinDate = this.value;
      if (checkinDate) {
        fetchOrdersByDate(checkinDate);
      } else {
        fetchOrders(); // 如果日期選擇器清空，則顯示所有訂單
      }
    });
  }

  // 當搜尋按鈕被點擊時，根據訂購人名稱篩選訂單
  const searchForm = document.getElementById("searchForm");
  if (searchForm) {
    searchForm.addEventListener("submit", function (event) {
      event.preventDefault(); // 防止表單默認提交
      const userName = document.getElementById("userInput").value;
      if (userName) {
        fetchOrdersByUser(userName);
      }
    });
  }

  // 當點擊「全部訂單」按鈕時，顯示所有訂單
  const allOrderButton = document.querySelector(".allOrderButton");
  if (allOrderButton) {
    allOrderButton.addEventListener("click", function () {
      fetchOrders(); // 顯示所有訂單
    });
  }
});

// 獲取所有訂單
function fetchOrders() {
  fetch("http://localhost/happypet/happypet_back/public/api/hotel_orders_all")
    .then((response) => response.json())
    .then((data) => {
      renderOrders(data);
      // console.log('all orders = ', data);
    })
    .catch((error) => console.error("資料獲取失敗:", error));
}

// 根據日期篩選訂單
function fetchOrdersByDate(checkinDate) {
  fetch(
    `http://localhost/happypet/happypet_back/public/api/hotel_orders_day?checkin=${encodeURIComponent(
      checkinDate
    )}`
  )
    .then((response) => response.json())
    .then((data) => {
      const ordersNotContainer = document.getElementById("ordersNotContainer");
      const ordersTableBack = document.getElementById("ordersTableBack");

      // 清空表格內容和「查無訂單」的訊息
      ordersTableBack.innerHTML = "";
      ordersNotContainer.innerHTML = "";

      if (data.length === 0) {
        // 當沒有訂單時顯示訊息
        ordersNotContainer.innerHTML = "<p>查無訂單。</p>";
      } else {
        // 當有訂單時渲染訂單資料
        renderOrders(data);
        console.log("date orders= ", data);
      }
    })
    .catch((error) => console.error("資料獲取失敗:", error));
}

// 根據訂購人名稱篩選訂單
function fetchOrdersByUser(userName) {
  console.log("fetch orders:", userName);
  fetch(
    `http://localhost/happypet/happypet_back/public/api/hotel_orders_by_user?name=${encodeURIComponent(
      userName
    )}`
  )
    .then((response) => {
      return response.json(); // 確保返回 JSON 數據
    })
    .then((data) => {
      console.log("API Data:", data);
      const ordersNotContainer = document.getElementById("ordersNotContainer");
      const ordersTableBack = document.getElementById("ordersTableBack");

      // 清空表格內容和「查無訂單」的訊息
      ordersTableBack.innerHTML = "";
      ordersNotContainer.innerHTML = "";

      if (data.length === 0) {
        // 當沒有訂單時顯示訊息
        ordersNotContainer.innerHTML = "<p>查無訂單。</p>";
      } else {
        // 當有訂單時渲染訂單資料
        renderOrders(data);
      }
    })
    .catch((error) => console.error("資料獲取失敗:", error));
}

// 渲染訂單列表
function renderOrders(data) {
  const ordersTableBack = document.getElementById("ordersTableBack");
  ordersTableBack.innerHTML = ""; // 清空表格內容

  data?.forEach((order) => {
    // console.log('order = ', order);
    const row = document.createElement("tr");
    row.innerHTML = `
              <td>${order.oid}</td>
              <td>${order.user_name}</td>
              <td>${order.pet_name}</td>
              <td>${order.room_type}</td>
              <td>${order.checkin.replaceAll("-", "/")}</td>
              <td>${order.checkout.replaceAll("-", "/")}</td>`;
    ordersTableBack.appendChild(row);
  });
}
// document.addEventListener("DOMContentLoaded", function () {
//     // 初次加載時獲取所有訂單
//     fetchOrders();

//     const checkinInput = document.getElementById("checkin");

//     // 當日期選擇發生變化時，根據選定的日期篩選訂單
//     checkinInput.addEventListener("change", function () {
//         const checkinDate = this.value;
//         if (checkinDate) {
//             fetchOrdersByDate(checkinDate);
//         } else {
//             fetchOrders(); // 如果日期選擇器清空，則顯示所有訂單
//         }
//     });

//     // 綁定「全部訂單」按鈕的事件
//     const allOrderButton = document.getElementById('allOrderButton');
//     allOrderButton.addEventListener('click', function () {
//         fetchOrders(); // 按下「全部訂單」按鈕時顯示所有訂單
//     });
// });

// // 獲取所有訂單
// function fetchOrders() {
//     fetch("http://localhost/happypet_back/public/api/hotel_orders_all")
//         .then(response => response.json())
//         .then(data => {
//             renderOrders(data);
//             console.log('all orders = ', data);
//         })
//         .catch(error => console.error("資料獲取失敗:", error));
// }

// // 根據日期篩選訂單
// function fetchOrdersByDate(checkinDate) {
//     fetch(`http://localhost/happypet_back/public/api/hotel_orders_day?checkin=${encodeURIComponent(checkinDate)}`)
//         .then(response => response.json())
//         .then(data => {
//             const ordersNotContainer = document.getElementById("ordersNotContainer");
//             const ordersTableBack = document.getElementById("ordersTableBack");

//             // 清空表格內容和「查無訂單」的訊息
//             ordersTableBack.innerHTML = "";
//             ordersNotContainer.innerHTML = "";

//             if (data.length === 0) {
//                 // 當沒有訂單時顯示訊息
//                 ordersNotContainer.innerHTML = "<p>查無訂單。</p>";
//             } else {
//                 // 當有訂單時渲染訂單資料
//                 renderOrders(data);
//                 console.log('date orders= ', data);
//             }
//         })
//         .catch(error => console.error("資料獲取失敗:", error));
// }

// // 渲染訂單列表
// function renderOrders(data) {
//     const ordersTableBack = document.getElementById("ordersTableBack");
//     ordersTableBack.innerHTML = ""; // 清空表格內容

//     data?.forEach(order => {
//         const row = document.createElement("tr");
//         row.innerHTML = `
//             <td>${order.oid}</td>
//             <td>${order.user_name}</td>
//             <td>${order.pet_name}</td>
//             <td>${order.room_type}</td>
//             <td>${order.checkin}</td>
//             <td>${order.checkout}</td>`;
//         ordersTableBack.appendChild(row);
//     });
// }
