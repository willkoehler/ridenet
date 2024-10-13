// Modify RowEditor:
// 1. adjust column widths
// 2. added support for min height
// 3. discard changes when ESC key is pressed
// 4. destroy tooltip when row editor closes so it doesn't come back unexpectedly

Ext.ux.grid.RowEditorWCK = Ext.extend(Ext.ux.grid.RowEditor, {

    minHeight:10,   // *** WCK minHeight can be used to set minimum height for row editor

    verifyLayout: function(force){
        if(this.el && (this.isVisible() || force === true)){
            var row = this.grid.getView().getRow(this.rowIndex);
            this.setSize(Ext.fly(row).getWidth(), Math.max(Ext.fly(row).getHeight(), this.minHeight) + 9);  // *** WCK support for min height
            var cm = this.grid.colModel, fields = this.items.items;
            for(var i = 0, len = cm.getColumnCount(); i < len; i++){
                if(!cm.isHidden(i)){
                    var adjust = 0;
                    if(i === (len - 1)){
                        adjust += 3; // outer padding
                    } else{
                        adjust += 2; // *** WCK This works better than original value (1)
                    }
                    fields[i].show();
                    fields[i].setWidth(cm.getColumnWidth(i) - adjust);
                } else{
                    fields[i].hide();
                }
            }
            this.doLayout();
            this.positionButtons();
        }
    },

    initFields: function(){
        var cm = this.grid.getColumnModel(), pm = Ext.layout.ContainerLayout.prototype.parseMargins;
        this.removeAll(false);
        for(var i = 0, len = cm.getColumnCount(); i < len; i++){
            var c = cm.getColumnAt(i),
                ed = c.getEditor();
            if(!ed){
                ed = c.displayEditor || new Ext.form.DisplayField();
            }
            if(i == 0){
                ed.margins = pm('0 1 2 1');
            } else if(i == len - 1){
                ed.margins = pm('0 0 2 1');
            } else{
                if (Ext.isIE) {
                    ed.margins = pm('0 0 2 0');
                }
                else {
                    ed.margins = pm('0 0 2 0');     // *** WCK adjust padding to line up fields with columns
                }
            }
            ed.setWidth(cm.getColumnWidth(i));
            ed.column = c;
            if(ed.ownerCt !== this){
                ed.on('focus', this.ensureVisible, this);
                ed.on('specialkey', this.onKey, this);
            }
            this.insert(i, ed);
        }
        this.initialized = true;
    },

    onKey: function(f, e){
        if(e.getKey() === e.ENTER){
            this.stopEditing(true);
            e.stopEvent();      // VERY IMPORTANT - Prevent browser from handling event because we are going to delete the control
        }
        if(e.getKey() === e.ESC){   // *** WCK Added handler for ESC key
            this.stopEditing(false);
            e.stopEvent();      // VERY IMPORTANT - Prevent browser from handling event because we are going to delete the control
        }
    },

    stopMonitoring : function(){
        this.bound = false;
        if(this.tooltip){
        // *** WCK Destroy tooltip so it won't come back until we specifically create it
            this.tooltip.destroy();
            this.tooltip = null;
        }
    }
});
