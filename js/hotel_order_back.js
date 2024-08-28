// ordermodel 獲取模態框元素
const modal = document.getElementById("myModal");
const closeModalBtn = document.querySelector(".close");
const deleteBtn = document.querySelector(".delete");
const form = document.getElementById("orderForm");

// 獲取寵物名字元素
const petNames = document.querySelectorAll(
  ".inpetNamehotel, .outpetName, .nopetName"
);

// 開啟模態框
petNames.forEach((pet) => {
  pet.addEventListener("click", () => {
    modal.style.display = "block";
  });
});

// 關閉模態框
closeModalBtn.addEventListener("click", () => {
  modal.style.display = "none";
});

// 點擊模態框外部關閉模態框
window.addEventListener("click", (event) => {
  if (event.target === modal) {
    modal.style.display = "none";
  }
});
// 點擊取消按鈕關閉模態框
cancelBtn.addEventListener("click", () => {
  modal.style.display = "none";
});

// 按刪除訂單 -> 清除表單內容並離開model
deleteBtn.addEventListener("click", () => {
  //   modal.style.display = "none";
  if (confirm("確定要永久刪除嗎？")) {
    form.reset(); // 清空表單內容
  }
});

// model內資訊
document.getElementById("checkin").addEventListener("change", function () {
  const selectedDate = this.value;

  // 發送請求到後端 API
  fetch(
    `http://localhost/happypet/happypet_back/public/api/chooseRoomNumber?date=${selectedDate}`
  )
    .then((response) => {
      if (!response.ok) {
        throw new Error(`找不到寵物: ${response.statusText}`);
      }
      return response.json();
    })
    .then((data) => {
      console.log("Fetched Data:", data); // 輸出返回的數據
      clearRoomInfo();

      // 使用一個對象來跟蹤每個房間的寵物信息
      const roomInfo = {};

      data.forEach((order) => {
        const roomNumber = order.room_number;
        const petName = order.pet_name;

        if (!roomInfo[roomNumber]) {
          roomInfo[roomNumber] = { pets: [] }; // 這裡用 pets 陣列來保存所有寵物信息
        }

        // 將每個 pet_name 添加到對應房間的 pets 陣列中
        roomInfo[roomNumber].pets.push(petName);
      });

      console.log("Room Info:", roomInfo);

      // 更新房間元素的內容
      Object.keys(roomInfo).forEach((roomNumber) => {
        console.log("hello", Object);
        const roomElement = document.getElementById(roomNumber);
        if (roomElement) {
          const petsElement = roomElement.querySelector(".nopetName"); // 預設顏色

          if (petsElement) {
            petsElement.innerHTML =
              roomInfo[roomNumber].pets
                .map((petName) => `<p >${petName}</p>`)
                .join("") || "No pets";
            console.log(
              `顯示房號跟寵物資訊: ${roomNumber}: ${petsElement.innerHTML}`
            );
          } else {
            console.error(`找不到房號 ${roomNumber}`);
          }
        }
      });
    })
    .catch((error) => {
      console.error("找不到房間順序:", error);
    });
});

// 清空所有房間的房客信息
function clearRoomInfo() {
  const roomElements = document.querySelectorAll(".row .col-4");
  roomElements.forEach((room) => {
    const petsElement = room.querySelector(".nopetName");

    if (petsElement) {
      petsElement.textContent = "";
    }
  });
}

// 抓訂單號ok
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".pet-name").forEach((element) => {
    element.addEventListener("click", function () {
      // 獲取點擊的房間號碼
      const roomNumber = this.getAttribute("data-room-number");

      // 向後端發送請求，根據 roomNumber 獲取訂單編號
      fetch(
        `http://localhost/happypet/happypet_back/public/api/getOrderNumberByRoomNumber?room_number=${roomNumber}`
      )
        .then((response) => {
          if (!response.ok) {
          }
          return response.json();
        })
        .then((data) => {
          // 確認 data 中包含訂單編號
          if (data.order_number) {
            // 設定訂單編號到 input 框中
            document.getElementById("field1").value = data.order_number;
          } else {
            console.error("找不到Order number");
          }
        });
    });
  });
});

// 抓user_info
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".pet-name").forEach((element) => {
    element.addEventListener("click", function () {
      // 獲取點擊的房間號碼
      const roomNumber = this.getAttribute("data-room-number");

      // 確保房間號碼有效
      if (roomNumber) {
        // 向後端發送請求，根據 roomNumber 獲取訂單的 uid
        fetch(
          `http://localhost/happypet/happypet_back/public/api/getUidByRoomNumber?room_number=${roomNumber}`
        )
          .then((response) => {
            if (!response.ok) {
            }
            return response.json();
          })
          .then((orderData) => {
            console.log(orderData);
            // 確認 orderData 中包含 uid
            if (orderData.uid) {
              // 根據 uid 獲取用戶資料
              return fetch(
                `http://localhost/happypet/happypet_back/public/api/getUserDetailsByUid?uid=${orderData.uid}`
              );
            } else {
              throw new Error("找不到uid");
            }
          })
          .then((response) => {
            if (!response.ok) {
            }
            return response.json();
          })
          .then((userData) => {
            // 確認 userData 中包含用戶信息
            if (userData.cname && userData.cellphone) {
              // 設定用戶信息到 input 框中
              document.getElementById("field2").value = userData.cname;
              document.getElementById("field3").value = userData.cellphone;
            } else {
              console.error("找不到User information");
            }
          });
      }
    });
  });
});
// 抓寵物資料
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".pet-name").forEach((element) => {
    element.addEventListener("click", function () {
      // 獲取點擊的房間號碼
      const roomNumber = this.getAttribute("data-room-number");

      // 確保房間號碼有效
      if (roomNumber) {
        // 向後端發送請求，根據 roomNumber 獲取訂單的 pet_id
        fetch(
          `http://localhost/happypet/happypet_back/public/api/getPetIdByRoomNumber?room_number=${roomNumber}`
        )
          .then((response) => {
            if (!response.ok) {
              throw new Error(
                `Network response was not ok: ${response.statusText}`
              );
            }
            return response.json();
          })
          .then((orderData) => {
            // 確認 data 中包含 pet_id
            if (orderData.pet_id) {
              // 根據 pet_id 獲取寵物詳細信息
              return fetch(
                `http://localhost/happypet/happypet_back/public/api/getPetDetailsById?pet_id=${orderData.pet_id}`
              );
            } else {
              throw new Error("找不到Pet ID");
            }
          })
          .then((response) => {
            if (!response.ok) {
              throw new Error(
                `Network response was not ok: ${response.statusText}`
              );
            }
            return response.json();
          })
          .then((petData) => {
            // 確認 petData 中包含寵物信息
            if (petData) {
              // 設定寵物信息到 input 框中
              document.getElementById("field4").value = petData.pet_name || "0"; // 寵物名字
              document.getElementById("field5").value =
                petData.pet_species || "0"; // 寵物種類
              document.getElementById("field6").value =
                petData.pet_variety || "0"; // 寵物品種
              document.getElementById("field7").value =
                petData.pet_birthday || "0"; // 寵物生日
              document.getElementById("field8").value =
                petData.pet_gender || "0"; // 寵物性別
              document.getElementById("field9").value =
                petData.pet_weight || "0"; // 寵物體重
            } else {
              console.error("找不到Pet information");
            }
          })
          .then(() => {
            // 根據 roomNumber 獲取訂單詳細信息
            return fetch(
              `http://localhost/happypet/happypet_back/public/api/getOrderDetailsByRoomNumber?room_number=${roomNumber}`
            );
          })
          .then((response) => {
            if (!response.ok) {
            }
            return response.json();
          })
          .then((orderData) => {
            // 確認 orderData 中包含訂單信息
            if (orderData) {
              // 設定訂單信息到 input 框中
              document.getElementById("field10").value =
                orderData.room_number || "0";
              document.getElementById("field11").value =
                orderData.room_total || "0";
              document.getElementById("field12").value =
                orderData.checkin || "0";
              document.getElementById("field13").value =
                orderData.checkout || "0";
              document.getElementById("field14").value =
                orderData.sameroom || "0";
              document.getElementById("field15").value =
                orderData.nightday || "0";
            } else {
              console.error("找不到Order details");
            }
          })
          .catch((error) => {
            console.error("找不到資訊", error);
          });
      } else {
        console.error("找不到房號");
      }
    });
  });
});

// 定義房間狀態對應的 CSS 類名 ok
const statusClasses = {
  inhotel: "inpetNamehotel",
  nohotel: "nopetName",
  outhotel: "outpetName",
};

// 儲存已選擇元素的狀態
let selectedPetElement = null;

// 監聽點擊事件以選擇要更改背景顏色的寵物名稱
document.querySelectorAll("p[data-room-number]").forEach((element) => {
  element.addEventListener("click", () => {
    // 先移除上一個已選擇元素的背景顏色
    if (selectedPetElement) {
      Object.values(statusClasses).forEach((className) => {
        selectedPetElement.classList.remove(className);
      });
    }
    // 更新已選擇的寵物元素
    selectedPetElement = element;
    console.log("Selected element:", selectedPetElement);
  });
});

// 根據選擇的狀態更新背景顏色
function updateBackgroundColor() {
  const status = document.getElementById("status").value;

  console.log("更新背景顏色Status:", status);
  console.log("Selected pet element:", selectedPetElement);

  if (selectedPetElement) {
    // 清除該元素的所有狀態類別
    Object.values(statusClasses).forEach((className) => {
      selectedPetElement.classList.remove(className);
    });

    // 依據選擇的狀態新增對應的背景顏色
    if (statusClasses[status]) {
      selectedPetElement.classList.add(statusClasses[status]);
    }
  }
}

// 監聽儲存按鈕點擊事件
const saveBtn = document.getElementById("saveBtn");
saveBtn.addEventListener("click", (event) => {
  event.preventDefault();

  // 更新背景顏色
  updateBackgroundColor();

  // 清空 selectedPetElement 以防下次未選中時影響結果
  selectedPetElement = null;

  // 關閉模態窗口並提示儲存成功
  const modal = document.getElementById("myModal");
  if (modal) {
    modal.style.display = "none";
  }
  alert("儲存成功！");
});
