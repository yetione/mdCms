var BlogRightColumnController = function($scope, $rootScope, BackendService, EntityFactory, $idialog){
    var self = this;

    self.category = {};

    self.deleteCategory = deleteCategory;

    activate();
    function activate(){
        console.log('Activate');
    }

    $scope.$on('Blog:categorySelected', onCategorySelected);
    function onCategorySelected($event, category) {
        self.category = category;
    }

    function deleteCategory() {
        BackendService.get({module:'Blog', controller:'Admin\\Category', action:'deleteItem', Id:self.category.Id}).then(function(response){
            var responseData = response.data;
            if (responseData.status == 'OK'){
                $idialog('message-dialog', {dialogId:'messageDialog', options:{message:'Категория удалена'}});
                $rootScope.$broadcast('blog.categoryDeleted', {category: self.category});
            }else {
                console.error('Ошибка при удалении категории.',responseData);
                $idialog('message-dialog', {dialogId:'messageDialog', options:{message:'При удалении возникла ошибка.', title:'Ошибка'}});
            }
        });
    }
};
adminApp.controller('blog.rightColumn', ['$scope', '$rootScope', 'BackendService', 'EntityFactory', '$idialog', BlogRightColumnController]);