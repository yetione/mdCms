var ListObject = function(options){
    var self = this;
    self.arrowImg = 'templates/admin/images/select_arrow_closing.png';

    self.items = [];
    self.previousItem = null;
    self.activeItem = -1;
    self.setItems = setItems;
    self.setActiveItem = setActiveItem;
    self.getActiveItem = getActiveItem;
    self.setNull = setNull;
    self.updateItems = updateItems;
    self.header = '';

    options = angular.extend({
        onItemSelect:function(i){},
        onSetNull:function(){},
        onUpdateItems:function(){}

    }, options);
    self.onItemSelect = options.onItemSelect ||  function(i){};
    self.onSetNull = options.onSetNull ||  function(){};
    self.onUpdateItems = options.onUpdateItems || function () {};


    function setItems(items){
        self.items = items;
    }

    function setActiveItem(index){
        if (isNaN(parseInt(index)) || index < 0 || index >= self.items.length){
            console.log('ListObject::setActiveItem: Index '+index+' is not valid.');
            return false;
        }
        if (self.activeItem != -1){
            self.previousItem = self.items[self.activeItem];
        }
        self.activeItem  = index;
        self.onItemSelect(index);
        return true;
    }

    function getActiveItem(){
        return self.activeItem == -1 ? undefined : self.items[self.activeItem];
    }

    function setNull(){
        self.onSetNull();
    }

    function updateItems() {
        self.onUpdateItems();
    }
};