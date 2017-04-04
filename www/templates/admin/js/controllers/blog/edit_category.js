var EditCategoryDialog = function($scope, UsersService, Utils, $idialog, BackendService, $rootScope){
    var self = this;
    self.options = {};
    self.category = {};
    self.currentUser = false;


    self.save = save;
    self.onDateChange = onDateChange;
    activate();
    function activate(){
        UsersService.getCurrentUser().then(function(user){
            self.currentUser = user;
        });
        self.category = $scope.dialogOptions.Category;

        //TODO: Category from server
        self.categoryStatuses = [{value:0,label:'Не опубликовано'}, {value:1, label:'Опубликовано'}];
        self.CSList = new ListObject({
            onItemSelect: function(){
                self.category.Public = self.CSList.getActiveItem().value;
            }
        });

        if (!self.category.CreationDate){
            self.category.CreationDate = moment();
        }
        self.CSList.setItems(self.categoryStatuses);
        self.CSList.setActiveItem(isNaN(parseInt(self.category.Public)) ? 1 : parseInt(self.category.Public));
    }

    function save(form) {
        if (!self.category.Name){
            $idialog('message-dialog',{dialogId:'messageDialog', options:{message:'Не заполнены обязательные поля.'}});
            return;
        }
        BackendService.send({data:self.category}, {module:'Blog', controller:'Admin\\Category', action:'saveCategory'}).then(function(response){
            var responseData = response.data;
            if (responseData.status == 'OK'){
                $scope.hide();
                $idialog('message-dialog', {dialogId:'messageDialog', options:{message:'Категория сохранена'}});
                $rootScope.$broadcast('Blog:EditCategoryDialog:saved', {category:responseData.data});
            }else{
                $idialog('message-dialog', {dialogId:'messageDialog', options:{title:'Ошибка', message:responseData.data.message}});
            }
            console.log(responseData);
        });
        //console.log(self.category);
    }
    
    function onDateChange() {

    }

};

adminApp.controller('blog.editCategoryDialog', ['$scope', 'UsersService', 'Utils', '$idialog', 'BackendService', '$rootScope', EditCategoryDialog]);