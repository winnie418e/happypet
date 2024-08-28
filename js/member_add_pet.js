document.getElementById('petImageChange').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const imgElement = document.getElementById('pet_headphoto');
            imgElement.src = e.target.result;
            imgElement.style.display = 'block'; // 顯示圖片預覽
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('petImageUpload').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const imgElement = document.getElementById('new_pet_headphoto');
            imgElement.src = e.target.result;
            imgElement.style.display = 'block'; // 顯示圖片預覽
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('btnAddPet').onclick = (event) => {
    event.preventDefault();

    const uid = localStorage.getItem('uid');

    let formData = new FormData(document.getElementById('formAddPet'));
    // let pet_gender = document.querySelector('input[name="pet_gender"]:checked').value;
    formData.append('uid', uid);

    console.log(uid);
    // console.log('寵名',add_pet_name.value);
    // console.log('犬貓',add_pet_species.value);
    // console.log('品種',add_pet_variety.value);
    // console.log('體重',add_pet_weight.value);
    // console.log('毛髮',add_pet_fur.value);
    // console.log('性別',pet_gender);
    // console.log('生日',add_pet_birthday.value);
    // console.log('節育',add_neutered.value);
    // console.log('備註',add_others.value);
    // console.log('照片',pet_headphoto.value);


    fetch('http://localhost/happypet/happypet_back/public/api/member_add_pet', {
        method: 'post',
        body: formData
        // headers: { 'Content-Type': 'application/json' },
        // body: JSON.stringify(
        //     {
        //         "uid": localStorage.getItem('uid'),
        //         // "pid": $(event.target)[0].dataset.pid,
        //         "pet_name": add_pet_name.value,
        //         "pet_species": add_pet_species.value,
        //         "pet_variety": add_pet_variety.value,
        //         "pet_weight": add_pet_weight.value,
        //         "pet_fur": add_pet_fur.value,
        //         "pet_gender": pet_gender,
        //         "pet_birthday": add_pet_birthday.value,
        //         "neutered": add_neutered.value,
        //         "others": add_others.value,
        //         "pet_headphoto": petImageUpload.value,
        //     }
        // )
    })
        .then(response => {
            console.log(response);

            if (!response.ok) {
                throw new Error(`伺服器錯誤(fetch回傳有問題): ${response.statusText}`);
            }
            return response.json();
        })
        .then((data) => {
            console.log('我是data1', data.message)
            if (data.message) {
                showAddPetModal(data.message);
            } else {
                showAddPetModal(data.error);
            }
        })
        .catch(error => {
            console.error("錯誤:", error);
            showAddPetModal("新增失敗，請稍後再試。");
        });

    function showAddPetModal(message) {
        $('#add_or_not_Modal').modal('show');
        document.getElementById('alert_message').innerText = message;
        if (message === "新增成功！") {
            setTimeout(() => {
                window.location.href = '../10_member/member_center.html';
            }, 2000); // 2秒延遲，讓用戶能看到成功消息
        }

    }
}