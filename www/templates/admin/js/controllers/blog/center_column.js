var BlogCenterColumnController = function ($scope, EntityFactory, $idialog, $filter, UsersService) {
    var self = this;
    self.categoriesManager = EntityFactory('BlogCategory');

    self.categories = [];
    self.activeItem = -1;
    self.currentUser = false;


    self.click = click;
    self.setActiveItem = setActiveItem;
    self.editCategory = editCategory;
    self.addCategory = addCategory;


    self.categoriesList = new ListObject({
        onItemSelect: function(){
            //console.log('SELECT');
            self.setActiveItem(self.categoriesList.activeItem);
            $scope.rightColumn.show('templates/admin/templates/blog/right_column.html').then(
                function(parentScope){
                    parentScope.$broadcast('Blog:categorySelected', self.categoriesList.getActiveItem());
                },
                null
            );
        }
    });

    activate();
    function activate(){
        UsersService.getCurrentUser().then(function(user){
            self.currentUser = user;
        });
        $scope.rightColumn.show('templates/admin/templates/blog/right_column.html');
        self.categoriesManager.getList().then(function(list){
            self.categories = list;
            self.categoriesList.setItems(self.categories);
            if (self.categories.length > 0){
                self.categoriesList.setActiveItem(0);
                //self.click(0);
            }

        });
        /*
        var p = EntityFactory('Product');
        p.getAll().then(function(products){
            var r = $filter('filter')(products, {CategoryId:undefined, Name:'рыба'});
            console.log(products, r);
        });

        */
    }

    function onCurrentUserChanged(event, data){
        console.log('CHANGED', data);
    }

    function click(index){
        //console.log(index);
        self.categoriesList.setActiveItem(index);
        /*if (self.setActiveItem(index)){
            self.editCategory(index);
        }*/
    }

    function setActiveItem(index){
        index = parseInt(index);
        if (!isNaN(index)){
            self.activeItem = index < self.categories.length && index > -1 ? index : -1;
            return true;
        }
        return false;
    }

    function editCategory(index){
        if (typeof self.categories[index] === 'undefined'){
            console.error('BlogCenterColumnController::editCategory: can not find category with index '+index);
            return false;
        }
        /*
        $idialog('dialogs/blog/edit-category',{dialogId:'EditBlogCategoryDialog',options:{
            Category:self.categories[index],
            urlPattern:self.urlPattern
        }});
        */


        $scope.rightColumn.show('templates/admin/templates/blog/right_column.html').then(
            function(parentScope){
                //console.log('resolve', self.categories[index]);
                parentScope.$broadcast('Blog:categorySelected', self.categories[index]);
            },
            null
        );

    }

    function addCategory(){
        self.categoriesManager.getEmpty().then(function(empty){
            console.log('empry', empty);

            $idialog('dialogs/blog/edit-category',{dialogId:'EditBlogCategoryDialog',options:{
                Category:empty,
                urlPattern:self.urlPattern
            }});


        });
    }

    $scope.$on('Blog:EditCategoryDialog:saved', onCategorySaved);
    function onCategorySaved($event, data) {
        var category = data.category;
        var isExists = self.categories.filter(function (item, i, arr) {
            return item.Id == category.Id;
        }).length == 1;
        console.log('data', data, isExists);
    }
};

adminApp.controller('blog.centerColumn', ['$scope', 'EntityFactory', '$idialog', '$filter', 'UsersService', BlogCenterColumnController]);