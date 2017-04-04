var ProductFilter = function(){
    var self = this;

    self.text = '';
    self.allType = {Id:0, Name:'Все категории'};
    self.type = self.allType;
    self.byType = byType;

    self.toggleList = toggleList;
    self.showList = false;
    self.arrowImg = 'templates/admin/images/select_arrow_closing.png';
    self.filter = filter;



    function byType(type, toggle){
        if (type === 'all'){
            self.type = self.allType;
        }else{
            self.type = type;
        }
        /*toggle = typeof toggle === 'undefined' ? true : toggle;
        if (toggle){
            self.toggleList();
        }*/
    }

    function toggleList(){
        self.showList = !self.showList;
        self.arrowImg = self.showList ? 'templates/admin/images/select_arrow_opening.png' : 'templates/admin/images/select_arrow_closing.png';
    }


    function filter(item){
        //console.log(item, parseInt(self.type.Id));
        var result = true;
        if (self.text.length > 0 && item.Name.toLowerCase().indexOf(self.text.toLowerCase()) == -1 && item.Description.toLowerCase().indexOf(self.text.toLowerCase()) == -1) result = false;
        if (parseInt(self.type.Id) > 0 && parseInt(item.TypeId) != parseInt(self.type.Id)) result = false;
        return result;

    }
};

var CustomSelectBox = function(defaultItem, onItemSelect){
    var self = this;

    self.showList = false;
    self.defaultItem = defaultItem;
    self.arrowImg = 'templates/admin/images/select_arrow_closing.png';
    self.toggleList = toggleList;
    self.setActiveItem = setActiveItem;
    self.onItemSelect = onItemSelect;
    self.activeItem = defaultItem;

    self.toggleList(false);

    function setActiveItem(item, toggle, fireEvent){
        self.activeItem = item;
        fireEvent = typeof fireEvent === 'boolean' ? fireEvent : true;
        if (typeof self.onItemSelect === 'function' && fireEvent){
            self.onItemSelect(item);
        }
        /*toggle = typeof toggle === 'undefined' ? true : toggle;
        if (toggle){
            self.toggleList();
        }*/
    }

    function toggleList(state){
        if (typeof state === 'boolean') self.showList = state;
        else self.showList = !self.showList;
        self.arrowImg = self.showList ? 'templates/admin/images/select_arrow_opening.png' : 'templates/admin/images/select_arrow_closing.png';
    }

};

var ShopRightColumnController = function($scope, dataService, urlConfigs, $idialog, FileUploader, $filter){
    var self = this;

    self.category = {};
    self.products = [];
    self.productTypes = [];



    var filterDefaultType = {Id:0,Name:'Все категории'};
    self.filter = {
        name: '',
        type:filterDefaultType
    };

    self.emptyProductImage = '';
    self.emptyProduct = {};
    self.currentProduct = {};

    self.onCategorySelected = onCategorySelected;
    self.loadProductTypes = loadProductTypes;
    self.loadProducts = loadProducts;
    self.saveProduct = saveProduct;
    self.setDefaultProductImage = setDefaultProductImage;
    self.editProduct = editProduct;
    self.addProduct = addProduct;
    self.removeProduct = removeProduct;

    self.editProductType = editProductType;
    self.addProductType = addProductType;

    self.getProductTypeById = getProductTypeById;


    self.activePrice = 'spb';
    self.editProductTitle = '';

    self.uploader = null;
    self.showProductThumb = false;

    self.filters = new ProductFilter();

    self.nullProductType = {Id:0,Name:'Категория'};
    self.productTypeList = new CustomSelectBox(self.nullProductType, function(type){
        self.currentProduct.Type = type;
        $scope.ProductEditForm.$setDirty();
    });


    self.onlyDigits = /^\-?\d+(\.\d{0,})?$/;


    angular.element(document.body).on('click', function(event){

        if (self.filters.showList){
            self.filters.toggleList();
            $scope.$apply();

        }
    });
    $scope.$on('shop.categorySelected', self.onCategorySelected);
    activate();
    function activate(){
        //self.loadProductTypes();

        self.uploader = new FileUploader({
            url: urlConfigs.buildUrl('admin/json.php', {module:'Food', controller:'Products', action:'changeImage'})
        });
        self.uploader.onAfterAddingFile = function(item){
            self.uploader.queue = [item];
            self.showProductThumb = false;
            $scope.ProductEditForm.$setDirty();
        };
        self.emptyProductImage = urlConfigs.buildUrl('uploads/empty_img.jpg');

        loadEmptyProduct().success(function(emptyProduct){
            self.addProduct(false);
        });
    }

    function onCategorySelected(event, category){
        self.category = category;
        self.loadProducts();
        self.loadProductTypes();
    }

    function loadProductTypes(){
        return dataService.get({module:'Food', controller:'ProductTypes', action:'getList', categoryId:self.category.Id}).success(function(response){
            self.productTypes = response.data;
            return self.productTypes;
        });
    }

    function loadProducts(){
        return dataService.get({module:'Food', controller:'Products', action:'getList', params:{CategoryId:self.category.Id}}).success(function(response){
            self.products = response.data;
            return self.products;
        });
    }

    function loadEmptyProduct(){
        return dataService.get({module:'Restful', controller:'Entity', action:'getEmptyEntity', entity:'Product'}).success(function(response){
            self.emptyProduct = response.data;
            //self.emptyProduct.Image = urlConfigs.buildUrl('uploads/empty_img.jpg');
            self.emptyProduct.Image = self.emptyProductImage;
            return self.emptyProduct;
        });
    }

    function setDefaultProductImage(){
        self.currentProduct.Image = self.emptyProductImage;
        self.uploader.queue = [];
        self.showProductThumb = true;
    }

    $scope.$on('iDialogShow', function(e, id) {
        if (id == 'messageDialog'){
            setTimeout(function(){
                e.currentScope.hide();
            }, 3000);
        }
    });

    function addProductType(){
        self.editProductType({Id:null,Name:null,CategoryId:self.category.Id});
    }

    function editProductType(type){
        var typeId = type.Id;
        var typeName = type.Name;
        var typeCategory = type.CategoryId;
        $idialog('edit-product-type-dialog',{dialogId:'productTypeEditDialog',options:{
            type:type,
            typeId:typeId,
            typeName:typeName,
            typeCategory:typeCategory,
            formSubmit:function($scope){

                type.Name = $scope.dialogCtrl.options.typeName;
                dataService.send({type:{Id:typeId,Name:$scope.dialogCtrl.options.typeName,CategoryId:typeCategory}}, {module:'Food',controller:'ProductTypes', action:'saveItem'}).success(function(data){
                    var message = 'Ошибка при сохранение категрии.';
                    if (data.result !== false){
                        message = 'Категория сохранена';
                        self.loadProductTypes();
                    }
                    $scope.hide();
                    $idialog('message-dialog',{dialogId:'messageDialog', options:{message:message}});
                });
            },
            remove:function($rScope){
                $idialog('confirm-dialog',{
                    dialogId: 'deleteProduct',
                    options:{message:'Удалить категорию (Будут удалены все связанные продукты)?', yesCb:function($scope){
                        console.log(typeId, type.Id);
                        dataService.get({module:'Food',controller:'ProductTypes',action:'deleteItem', typeId:typeId}).success(function(data){
                            $scope.hide();
                            console.log(data);
                            if (data.result){
                                $rScope.hide();
                                $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'Категория удалена.'}});

                                self.loadProductTypes();
                                self.loadProducts();
                                self.filters.byType('all', false);
                            }else{
                                $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'Ошибка при удалении.'}});
                            }
                        });
                    }}
                });
            },
            hide:function($scope){
                $scope.hide();
            }

        }});
    }

    function getProductTypeById(id){
        for (var i=0;i<self.productTypes.length;i++){
            if (parseInt(self.productTypes[i].Id) == id){
                return self.productTypes[i];
            }
        }
        return null;
    }

    function saveProduct(){
        var productData = {};
        if (!self.currentProduct.CategoryId){
            console.log('Product category set to current:'+self.category.Id);
            self.currentProduct.CategoryId = self.category.Id;
        }
        if (!self.currentProduct.PriceMsk && !self.currentProduct.PriceSpb){
            $idialog('message-dialog',{dialogId:'messageDialog', options:{message:'Пожалуйста, введите цену для одного из городов.'}});
            return;
        }else if (!self.currentProduct.PriceMsk || !self.currentProduct.PriceSpb){
            if (!self.currentProduct.PriceMsk) self.currentProduct.PriceMsk = self.currentProduct.PriceSpb;
            else self.currentProduct.PriceSpb = self.currentProduct.PriceMsk;
        }

        self.currentProduct.TypeId = self.currentProduct.Type.Id;

        dataService.send({product:self.currentProduct}, {module:'Food', controller:'Products', action:'saveProduct'}).success(function(data){

            var showSuccessDialog = false;
            if (data.result === false){
                $idialog('message-dialog',{dialogId:'messageDialog', options:{message:'Ошибка при сохранение продукта.'}});
                console.log('ERR',data);
                return data;
            }


            if (self.uploader.queue.length > 0){
                self.uploader.onBeforeUploadItem = function(item){
                    item.url = urlConfigs.buildUrl('admin/json.php', {module:'Food', controller:'Products', action:'changeImage', productId:data.result.Id});
                };
                self.uploader.onSuccessItem = function(item, response, status, headers){
                    $idialog('message-dialog',{
                        dialogId: 'messageDialog',
                        options:{message:'Продукт сохранен.'}
                    });
                    $scope.ProductEditForm.$setPristine();
                    self.currentProduct.Image = urlConfigs.buildUrl(response.url.filePath);
                    self.showProductThumb = true;
                    self.loadProducts();
                    //$rootScope.$broadcast('Product.productSaved',{product:data.result});
                };
                self.uploader.onCompleteAll = function(){
                    self.uploader.clearQueue();
                };
                self.uploader.uploadAll();
            }else{
                $idialog('message-dialog',{
                    dialogId: 'messageDialog',
                    options:{message:'Продукт сохранен.'}
                });
                $scope.ProductEditForm.$setPristine();
                self.loadProducts();
            }

            self.currentProduct = data.result;
            self.currentProduct.Type = getProductTypeById(parseInt(self.currentProduct.TypeId));

            console.log('SAVE', self.currentProduct);
            return data;
        });
    }

    function removeProduct(id){
        $idialog('confirm-dialog',{
            dialogId: 'deleteProduct',
            options:{message:'Удалить продукт?', yesCb:function($scope){
                dataService.get({module:'Food',controller:'Products',action:'deleteItem', productId:id}).success(function(data){
                    $scope.hide();
                    if (data.result){
                        $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'Продукт удален.'}});
                        self.loadProducts();
                        //$scope.ProductEditForm.$setPristine();
                        self.addProduct();
                    }else{
                        $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'Ошибка при удалении.'}});
                    }
                });
            }}
        });

    }

    function addProduct(focus){
        var action = function(){
            self.editProductTitle = 'Добавить продукт';
            self.uploader.clearQueue();
            $scope.ProductEditForm.$setPristine();
            self.productTypeList.setActiveItem(self.nullProductType, false, false);
            //self.currentProduct = self.emptyProduct;
            angular.copy(self.emptyProduct, self.currentProduct);
            self.currentProduct.Id = 0;
            self.showProductThumb = true;
            focus = typeof focus === 'boolean' ? focus : true;

            if (focus){
                var element = document.getElementById('product-name');
                angular.element(element)[0].focus();
                console.log(element.offsetTop);
                window.scrollTo(0, element.offsetTop);
            }
        };

        if ($scope.ProductEditForm.$dirty){
            $idialog('confirm-dialog',{dialogId:'resumeWithoutSave',options:{
                message:'Данные были изменены. Продолжить без сохранения?',
                yesCb:function($scope){
                    action();
                    $scope.hide();
                }
            }});
        }else{
            action();
        }


    }

    function getProductById(id){
        for (var i=0;i<self.products.length;i++){
            if (parseInt(self.products[i].Id) == id){
                return self.products[i];
            }
        }
        return null;
    }

    function editProduct(id, focus){
        var product = getProductById(parseInt(id));
        if (product){
            if (parseInt(self.currentProduct.Id) == parseInt(id)){
                return;
            }
            var action = function(){
                angular.copy(product, self.currentProduct);
                //self.currentProduct = product;
                self.currentProduct.Type = getProductTypeById(product.TypeId);
                self.productTypeList.setActiveItem(self.currentProduct.Type, false, false);
                self.currentProduct.Image = urlConfigs.buildUrl(product.Image);
                self.editProductTitle = 'Редактирование товара';
                $scope.ProductEditForm.$setPristine();
                self.uploader.clearQueue();
                focus = typeof focus === 'boolean' ? focus : true;
                angular.element(document.getElementById('product-name'))[0].focus();
            };
            if($scope.ProductEditForm.$dirty){
                $idialog('confirm-dialog',{dialogId:'resumeWithoutSave',options:{
                    message:'Данные были изменены. Продолжить без сохранения?',
                    yesCb:function($scope){
                        action();
                        $scope.hide();
                    }
                }});
            }else{
                action();
            }
        }else{
            $idialog('message-dialog',{dialogId: 'messageDialog', options:{message:'Ошибка при выборе продукта с ID:'+id}});
        }
    }


};

adminApp.controller('shop.rightColumn', ['$scope', 'adminDataService', 'UrlConfigs', '$idialog', 'FileUploader', '$filter', ShopRightColumnController]);