// question
function toggleText(id) {
  var element = document.getElementById(id);
  if (element.style.display === "none" || element.style.display === "") {
    element.style.display = "block";
  } else {
    element.style.display = "none";
  }
}

document.addEventListener("DOMContentLoaded", function () {
  // 從 localStorage 中取得存儲的訂單陣列，確保它是一個數組
  let orders = JSON.parse(localStorage.getItem("hotelorders")) || [];

  // 渲染訂單列表的函數
  function renderOrders() {
    const ordersTable = document.getElementById("ordersTable");
    ordersTable.innerHTML = ""; // 清空表格內容

    orders.forEach((orderDetails, index) => {
      // 生成表格的 HTML
      const rowHTML = `
              <tr>
                <td class="pt-4">
                  <p>入住日期 ${orderDetails.checkinDisplay}</p>
                  <p>退房日期 ${orderDetails.checkoutDisplay}</p>
                </td>
                <td>${orderDetails.hotelName}</td>
                <td>${orderDetails.hotelCartQuantity}</td>
                <td>${orderDetails.hotelPetName}</td>
                <td>${orderDetails.sameRoomCount}</td>
                <td>${orderDetails.nightdayDisplay}</td>
                <td>NT$${Number(orderDetails.roomTotal).toLocaleString(
                  "en-US"
                )}</td>
                <td>
                  <button class="btn btn-outline-secondary btn-lg delete-btn" data-index="${index}">
                    <i class="bi bi-trash3-fill"></i>
                  </button>
                </td>
              </tr>
            `;

      // 插入新生成的 HTML 到 tbody 中
      ordersTable.innerHTML += rowHTML;
    });

    // 計算所有訂單的總金額
    const totalAmount = orders.reduce(
      (sum, order) => sum + parseFloat(order.roomTotal),
      0
    );

    // 格式化總金額
    const formattedTotalAmount = totalAmount.toLocaleString("en-US");

    // 顯示總金額
    document.querySelector(
      ".text-end span"
    ).innerText = `總金額：NT$${formattedTotalAmount}`;
  }

  // 渲染訂單列表
  renderOrders();

  // 監聽刪除按鈕點擊事件
  document
    .getElementById("ordersTable")
    .addEventListener("click", function (event) {
      if (event.target && event.target.closest(".delete-btn")) {
        const button = event.target.closest(".delete-btn");
        const index = button.getAttribute("data-index");

        // 刪除該索引的訂單
        orders.splice(index, 1);

        // 更新 localStorage
        localStorage.setItem("hotelorders", JSON.stringify(orders));

        // 重新渲染訂單列表
        renderOrders();
      }
    });

  // 存訂房資訊到 localStorage 並跳轉到 hotel.html
  document
    .getElementById("orderMoreRooms")
    .addEventListener("click", function () {
      // 更新 localStorage 以便在跳轉後保留訂單
      localStorage.setItem("hotelorders", JSON.stringify(orders));
      // 導航到 hotel.html
      window.location.href = "hotel.html";
    });
});

document.getElementById("nextStep").addEventListener("click", function () {
  let orders = JSON.parse(localStorage.getItem("hotelorders")) || [];
  const selectedPetIds = localStorage.getItem("selectedPetIds") || ""; // 獲取選中的寵物ID

  // 將選中的寵物ID轉換為陣列
  let petIdsArray = selectedPetIds.split(", ").filter((id) => id);
  console.log("sssss", petIdsArray);
  petIdsArray = petIdsArray.join(","); // 將陣列轉換為逗號分隔的字串

  // 傳送訂單和選中的寵物ID
  fetch("http://localhost/happypet/happypet_back/public/api/hotel_orders", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ hotelorders: orders, petIds: petIdsArray }), // 確保這裡傳送了 `hotelorders` 和 `petIds`
  })
    .then((response) => response.json())
    .then((data) => {
      console.log("訂單傳送成功", data);
      console.log("訂單傳送成功:", data);
      // 清空 hotelorder.html 的訂單
      localStorage.removeItem("hotelorders");
      // 清空其他資料，保留選中的寵物資訊
      const selectedPetIds = localStorage.getItem("selectedPetIds");
      const selectedPetNames = localStorage.getItem("selectedPetNames");
      localStorage.clear();
      if (selectedPetIds && selectedPetNames) {
        localStorage.setItem("selectedPetIds", selectedPetIds);
        localStorage.setItem("selectedPetNames", selectedPetNames);
      }
      document.getElementById("ordersTable").innerHTML = "";
      document.querySelector(".text-end span").innerText = "總金額：NT$0";
    })
    .catch((error) => {
      console.error("訂單傳送失敗:", error);
    });
});

// progress
document.addEventListener("DOMContentLoaded", function () {
  const steps = document.querySelectorAll(".progress-step");
  // 初始化
  let currentStep = 0;

  document.getElementById("nextStep").addEventListener("click", function () {
    // 檢查當前步驟索引是否小於步驟總數減一
    if (currentStep < steps.length - 1) {
      // 移除當前步驟的 active 類別
      steps[currentStep].classList.remove("active");
      // 進入下一步
      currentStep++;
      // 為新的當前步驟添加 active 類別
      steps[currentStep].classList.add("active");
    }
  });
});
