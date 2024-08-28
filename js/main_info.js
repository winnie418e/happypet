// let insertBtn = document.getElementById('insertBtn')
    window.onload = ()=>{

        let myModal = document.getElementById('myModal')
        let myInput = document.getElementById('myInput')
        // let infoBtn = document.querySelector('.bi-info-circle-fill')
        myModal.addEventListener('shown.bs.modal', function () {
            if (myInput != undefined) {
                myInput.focus()
            }
        })
        function showMsg(msg){
            $('#myModal').modal('show')
            $('#alertMsg').text(msg)
        }
        $('.bi-info-circle-fill').click(()=>{
            $(".descriptionInfo").removeClass('d-none');
        })
        $('.bi-x-circle-fill').click(()=>{
            $(".descriptionInfo").addClass('d-none');
        })
        let pdinfoType = pdInfo.getAttribute('data-type');
        console.log('pdinfoType',pdinfoType)
        $('#insertPage').addClass('active')

        insertPage.onclick = ()=>{
            $('#maininfoTitle').text('產品主要資訊(新增)')
            $('#updateBtn').addClass('d-none')
            $('#insertBtn').removeClass('d-none')
            pdInfo.setAttribute('data-type','insert')
            pdinfoType = pdInfo.getAttribute('data-type')

            console.log('pdinfoType',pdinfoType)
            $('#insertPage').addClass('active')
            $('#updatePage').removeClass('active')
            clearInput()
            $('#pdSeries').val('')


        }
        updatePage.onclick = ()=>{
            edit()
            // showProduct()

        }
        const urlParams = new URLSearchParams(window.location.search);
        const mode = urlParams.get('mode') 
        const seriesID = urlParams.get('id') 
        if (mode === 'edit' && seriesID) {
            console.log('mode裡',seriesID)
            edit()
            $('#pdSeries').val(seriesID)
            showProduct(seriesID)
            console.log('mode裡pdSeries',pdSeries.innerText)

        }
        // document.addEventListener('DOMContentLoaded', function() {
            // console.log('DOM 已加載完成');
            function edit(){
                
                // updatePage.onclick = ()=>{
                $('#maininfoTitle').text('產品主要資訊(修改)')
                $('#insertBtn').addClass('d-none')
                $('#updateBtn').removeClass('d-none')
                pdInfo.setAttribute('data-type','update')
                pdinfoType = pdInfo.getAttribute('data-type')
                $('#insertPage').removeClass('active')
                $('#updatePage').addClass('active')
                console.log('pdinfoType',pdinfoType)
                $('#pdSeries').attr("placeholder", "請搜尋產品系列編號");
                // }
            }
        // });
        function clearInput(){
            $('#categoryOptions').val('default')
            $('#pdName').val('')
            $('#showCoverImg').html('');
            $('#showOthersImgs').html('');
            $('#showDescImgs').html('');
            $('input[name^="description"]').each((_,elem)=>{
                $(elem).val('')
            })

        }
        // 查詢產品系列編號是否使用

        pdSeries.onchange = (event)=>{
       
            console.log('pdSeries裡pdinfoType',pdinfoType)
            if(pdinfoType === 'insert'){
                console.log('event.target.value',event.target.value)
                // fetch(`http://localhost/testpet/public/api/product_back/info/select/${event.target.value}`,{
                fetch(`http://localhost/happypet/happypet_back/public/api/product_back/info/select/${event.target.value}?pdinfoType=${pdinfoType}`,{
                    method:'get',
                    // body:formData
                })
                .then(response=>{
                    // console.log(response)
                    // return response.json()
                    return response.json()
                })
                .then(({message,categories})=>{
                // .then((dataa)=>{

                    // console.log('dataa',dataa)
                    // console.log('message = ',message.message)
                    // let {message,data} = data
                    if (message != undefined) {
                        console.log('我是message.message')
                        showMsg(message.message)
                    }
                })
                console.log('我是if')
            }else{
                console.log('我是else')
                showProduct(event.target.value)
            }

        }
        function showProduct(seriesID){
            fetch(`http://localhost/happypet/happypet_back/public/api/product_back/info/update/${seriesID}`,{
                method:'post',
                // body:formData
            })
            .then(response=>{
                // console.log(response)
                return response.json()
            })
            .then(({seriesProduct,message})=>{ 
                // console.log('測試 = ',seriesProduct +"與message"+message)
                // showOthersImgs.innerHTML = '';
                // showDescImgs.innerHTML = '';
                clearInput()

                // 如果系列編號不存在即顯示是訊息
                if(message){
                    // message ? showMsg(message): null ;
                    showMsg(message)
                    // $('select').val('')
                    // $('#pdName').val('')
                    // $('#showCoverImg').html('');
                    // $('#showOthersImgs').html('');
                    // $('#showDescImgs').html('');
                    // $('input[name^="description"]').each((_,elem)=>{
                    //     $(elem).val('')
                    // })
                    clearInput()
                    return
                }

                console.log('seriesProduct = ',seriesProduct)
                seriesProduct.forEach((seriesPD)=>{
                    let {category_id,series_name,pic_category_id,...products} = seriesPD

                    // console.log('data',data)
                    // console.log('category_id',category_id)
                    // console.log('series_name',series_name)
                    // pdName.value = series_name
                    $('#pdName').val(series_name)

                    // categoryOptions.value = category_id
                    $('#categoryOptions').val(category_id)
                    // console.log('inpuuuuut',$('input[name^="description"]'))
                    $('input[name^="description"]').each((i,elem)=>{
                        descKey = `description${i+1}` 
                        // console.log("seriesProduct[key]",products[descKey])
                        $(elem).val(products[descKey])
                    })
                    // console.log('pic_category_id',pic_category_id)
                    // seriesProduct.forEach((elem,i)=>{
                        // console.log('seriesProduct.elem',elem)
                        
                        switch (pic_category_id) {
                            case 1:
                                showCoverImg.innerHTML = `<img src="${seriesPD.img}">`
                                break;
                            case 2:
                                showOthersImgs.innerHTML += `<img src="${seriesPD.img}">`
                                break;
                            case 3:
                              
                                showDescImgs.innerHTML += `<img src="${seriesPD.img}">`
                                break;
                        
                            default:
                                break;
                        }
                })
            })
        }
        updateBtn.addEventListener('click', (event)=>{
            event.preventDefault();
            console.log('Update button clicked, preventing default form submission');
            let formData = new FormData(document.getElementById('pdInfo'));
            for (let pair of formData.entries()) {
                console.log('我是formdata',pair[0] + ': ' + pair[1]); 
            }
            fetch(`http://localhost/happypet/happypet_back/public/api/product_back/info/modify`,{
                method:'post',
                body:formData,
                // headers: {
                //     'X-Requested-With': 'XMLHttpRequest', // 確保伺服器知道這是一個 AJAX 請求
                // }
            })
            .then(response=>{
                // console.log(response)
                // return response.json()
                return response.json()
            })
            .then((data)=>{
                // console.log('data',data)
                if (data.message) {
                    // console.log(data.message);
                    showMsg(data.message)
                    setTimeout(()=>{
                        location.reload()// 刷新頁面
                        window.location.href = `http://localhost/happypet/happypet_front/40_product/back/main_info.html`;
                    },2000)
                } else if (data.error) {
                    showMsg(data.error)
                }
             })
        })

        coverimg.onchange = ()=>{
            let imgreader = new FileReader();
            console.log('imgreader----->',imgreader)
            console.log('imgreader----->',imgreader)
            imgreader.readAsDataURL(coverimg.files[0])
            showCoverImg.innerHTML = ''
            imgreader.onload = (event)=>{
                // console.log('eee',event) 
                // src在event裡面的target的result
                showCoverImg.innerHTML += `<img src="${event.target.result}">`
            }
           
            // console.log('我是封面圖',descimgs.files[0])
        }
        
        descimgs.onchange = ()=>{
            // console.log('我是敘述圖',descimgs.files)
            let descriptionIMGs = Array.from(descimgs.files); // 轉換為陣列
            descriptionIMGs.forEach((img)=>{
                // console.log('我是敘述圖單張',img)
                let imgreader = new FileReader();
                imgreader.readAsDataURL(img)
                showDescImgs.innerHTML = ''
                imgreader.onload = (event)=>{
                    // console.log('eee',event) 
                    // src在event裡面的target的result
                    showDescImgs.innerHTML += `<img src="${event.target.result}">`
                }
            })
            // console.log('我是封面圖',descimgs.files[0])
        }
        othersImgs.onchange = ()=>{
            // console.log('othersImgs.files---->',othersImgs.files)
            let othersAllIMGs = Array.from(othersImgs.files); // 轉換為陣列
            othersAllIMGs.forEach((img)=>{
                let imgreader = new FileReader();
                imgreader.readAsDataURL(img)
                showOthersImgs.innerHTML = ''
                imgreader.onload = (event)=>{
                    // console.log('eee',event) 
                    // src在event裡面的target的result
                    showOthersImgs.innerHTML += `<img src="${event.target.result}">`
                }
            })
            // console.log('我是封面圖',descimgs.files[0])
        }
        
        // 查詢option列出的類別
        // fetch('http://localhost/testpet/public/product_back/info/select',{
        fetch('http://localhost/happypet/happypet_back/public/api/product_back/info/select',{
                method:'get',
        })
        .then(response=>{
            // console.log(response)
            // return response.text()
            return response.json()
        })
        .then(({categories})=>{
        // .then((aaa)=>{
            // alert(data)
            console.log('我是options',categories)
            // console.log('我是options',aaa)
            categories.forEach((category) => {   //多張照片遍歷
                // console.log('category = ', category)
                // console.log('我是index',index)
                // console.log(category.split('-'))  //分割後取英文([0])
                categoryOptions.innerHTML += `<option value="${category.split('-')[0]}">${category}</option>`
            });
        })


        // insertBtn.onclick = (event)=>{
        insertBtn.addEventListener('click', (event)=>{

            event.preventDefault();
            let formData = new FormData(document.getElementById('pdInfo'));
            formData.append('action', 'insert');
            console.log('formdata',formData)
            formData.forEach((value, key) => {
                console.log('formdata',key, value);
            });
            fetch('http://localhost/happypet/happypet_back/public/api/product_back/info/create',{
                method:'post',
                body:formData
            })
            .then(response=>{
                if (!response.ok) {
                    throw new Error(`伺服器錯誤: ${response.statusText}`);
                }
                // console.log(response)
                // return response.text()
                return response.json()
            })
            .then(data=>{
                // alert(data)
                console.log('我是data1',data)
                console.log('我是data1',data.message)
                if (data.message) {
                    console.log(data.message);
                    showMsg(data.message)
                    setTimeout(()=>{
                        console.log('成功與否')
                        location.reload()// 刷新頁面
                    },2000)
                } else if (data.error) {
                    console.log('data.error',data.error)
                    showMsg(data.error)
                }
          
            })
        // }
        })
    }