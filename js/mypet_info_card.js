// let uid = localStorage.getItem('uid');
// let uid = 1;

const uid = localStorage.getItem('uid');

if (!uid) {
    // 如果找不到 uid，可能使用者未登入或 localStorage 已被清除
    alert('未登入，請重新登入。');
    // window.location.href = '../00_index/index.html'; // 導向首頁
    // window.location.href = '../10_member/member_center.html'; // 導向首頁
}

//編輯寵物資料，儲存修改的資料
document.getElementById('btnEditMyPet').onclick = (event) => {
    event.preventDefault();
    // console.log('hello', $(event.target)[0].dataset.pid);

    let form = document.getElementById('formMyPetEdit');
    if (!form) {
        console.error("找不到表單元素！");
        return;
    }

    let formData = new FormData(form);
    formData.append('pid', $(event.target)[0].dataset.pid);
    formData.append('pet_gender', document.querySelector('input[name="pet_gender"]:checked').value);

    fetch('http://localhost/happypet/happypet_back/public/api/member_edit_pet', {
        method: 'post',
        body: formData
    })
    
    // let pet_gender = document.querySelector('input[name="pet_gender"]:checked').value;
    // fetch('http://localhost/happypet/happypet_back/public/api/member_edit_pet', {
    //     method: 'post',
    //     headers:{'Content-Type':'application/json'},
    //     body: JSON.stringify(
    //         {
    //             "pid": $(event.target)[0].dataset.pid,
    //             "pet_name":pet_name.value,
    //             "pet_species":pet_species.value,
    //             "pet_variety":pet_variety.value,
    //             "pet_weight":pet_weight.value,
    //             "pet_fur":pet_fur.value,
    //             "pet_gender":pet_gender,
    //             "pet_birthday":pet_birthday.value,
    //             "neutered":neutered.value,
    //             "others":others.value,
    //             "pet_headphoto":pet_headphoto.value,
    //         }
    //     )

    // })
        .then(response => {
            console.log(response);

            if (!response.ok) {
                throw new Error(`伺服器錯誤(fetch回傳有問題): ${response.statusText}`);
            }
            return response.json();
        })
        .then((data) => {
            console.log('我是data2', data.message)
            if (data.message) {
                showAddPetModal(data.message);
            } else {
                showAddPetModal(data.error);
            }
        })
        .catch(error => {
            console.error("錯誤:", error);
            showAddPetModal("寵物資料更新失敗，請稍後再試。");
        });

    function showAddPetModal(message) {
        $('#add_or_not_Modal').modal('show');
        document.getElementById('alert_message').innerText = message;
        if (message === "新資料儲存成功！"|| message === "資料已儲存") {
            setTimeout(() => {
                window.location.href = '../10_member/member_center.html';
            }, 2000); // 2秒延遲，讓用戶能看到成功消息
        }

    }
}

//查看寵物在資料庫的資料
$('#mypet_card').on('click', '.pet_card_check_detail', function () {
    console.log($('#btnEditMyPet'));
    // console.log($(this).data('pid'));
    // console.log($('#btnEditMyPet').data('pid'));

    //編輯寵物資料的按鈕要抓到該寵物的pid
    btnEditMyPet.dataset.pid = $(this).data('pid');
    // $('#btnEditMyPet').data('pid', $(this).data('pid'));
    // console.log('card');
    // document.getElementById('pet_name').value = $(this).data('pet_name');

    pet_name.value = $(this).data('pet_name');
    pet_species.value = $(this).data('pet_species');
    // console.log(pet_species.value);
    pet_variety.value = $(this).data('pet_variety');
    pet_weight.value = $(this).data('pet_weight');
    pet_fur.value = $(this).data('pet_fur');
    pet_birthday.value = $(this).data('pet_birthday');
    // 這樣寫顯示不出來
    // pet_gender.value = $(this).data('pet_gender');
    // 要改成判斷資料的值，決定勾選哪一個input的id
    if ($(this).data('pet_gender') == '公') {
        male.checked = true
    } else {
        female.checked = true
    }
    neutered.value = $(this).data('neutered');
    others.value = $(this).data('others');
    // pet_headphoto.src = $(this).data('pet_headphoto');
    // pet_headphoto.src ? $(this).data('pet_headphoto') : "../img/10_member/petheadshot_none.png"
    pet_headphoto.src = $(this).data('pet_headphoto') ? $(this).data('pet_headphoto') : "../img/10_member/petheadshot_none.png";

})

//動態生成我的寵物卡片
function show(mymypetdata) {

    mypet_card.innerHTML = ''; //每次生成前都先清空
    //寫好的HTML拿來改
    // <div class="col">
    //     <div class="card h-100">
    //         <img src="../img/10_member/pet-money.jpg" class="card-img-top" alt="..."/>
    //             <div class="card-body">
    //                 <h5 class="card-title" id="mypet_name">Money</h5>
    //                 <p class="card-text">品種：柴犬</p>
    //                 <p class="card-text">生日：2020/08/01</p>
    //             </div>
    //             <div class="card-footer d-flex justify-content-center gap-2">
    //                 <button class="btn btn-secondary btn-sm pet_card_check_detail">查看詳情</button>
    //                 <button class="btn btn-secondary btn-sm">編輯</button>
    //             </div>
    //     </div>
    // </div>
    mymypetdata.forEach(element => {
        // console.log(element.pet_variety);
        // console.log(element.pet_headphoto);

        mypet_card.innerHTML += `
            <div class="col">
                <div class="card h-100">
                    <img src="${element.pet_headphoto ? element.pet_headphoto : "../img/10_member/petheadshot_none.png"}" class="card-img-top" alt="..."/>
                    <div class="card-body">
                        <h5 class="card-title">${element.pet_name}</h5>
                        <p class="card-text">品種：${element.pet_variety}</p>
                        <p class="card-text">生日：${element.pet_birthday.replaceAll('-', '/')}</p>
                    </div>
                    <div class="card-footer d-flex justify-content-center gap-2">
                        <button class="btn btn-secondary btn-sm pet_card_check_detail" data-pid="${element.pid}" data-pet_name = ${element.pet_name} data-pet_species = ${element.pet_species} data-pet_variety = ${element.pet_variety} data-pet_weight = ${element.pet_weight} data-pet_fur = ${element.pet_fur} data-pet_birthday = ${element.pet_birthday} data-pet_gender = ${element.pet_gender} data-neutered=${element.neutered} data-others=${element.others} data-pet_headphoto=${element.pet_headphoto} data-bs-toggle="modal" data-bs-target="#showModal"">編輯資料</button>
                    </div>
                </div>
            </div>`

    });
    //把新增功能卡片貼在我的寵物卡片後面
    mypet_card.innerHTML += `<a data-bs-toggle="modal" data-bs-target="#addPetModal" href="#" class="card-add">
                            <div class="col card h-100">
                                <i class="bi bi-plus-circle-dotted"></i>
                                <div class="card-body mt-5">
                                    <h5 class="card-title mt-2">Add</h5>
                                    <p class="card-text">點此新增</p>
                                </div>
                                <div class="card-footer d-flex justify-content-center gap-2">
                                    <button class="btn btn-secondary btn-sm">新增寵物</button>
                                </div>
                            </div>
                        </a>`

}

//串接我的寵物資料API
function mypet_info_card() {

    fetch(`http://localhost/happypet/happypet_back/public/api/member_mypet`, {
        method: 'post',
        body: JSON.stringify({ uid: uid }),
        headers: { 'Content-Type': 'application/json' }

    })
        .then(response => response.json())
        .then(mypetdata => {
            console.log(mypetdata);
            show(mypetdata)
        })
}

mypet_info_card()

