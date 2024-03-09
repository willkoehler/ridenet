// --- Build array containing contents of query string
var querystring = window.location.search.substring(1);
var g_HTMLRequest = Ext.urlDecode(querystring);

// --- Add trim() functions to string class
String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g,"");
}
String.prototype.ltrim = function() {
	return this.replace(/^\s+/,"");
}
String.prototype.rtrim = function() {
	return this.replace(/\s+$/,"");
}


// convert LF, CR, CRLF, to HTML line breaks <br>
function LF2BR(dataStr)
{
    return (dataStr!=null) ? dataStr.replace(/(\r\n|[\r\n])/g, "<br />") : null;
}


// Force browser to reload a dynamic image and refresh the cached copy by placing
// the image in a hidden iframe and then refreshing the iframe. This function
// requires a file DynamicImages
function forceReload(cachebuster, url)
{
    var id = url.replace(/[^a-zA-Z 0-9]+/g,"");     // remove non-alphanumerics
    if(Ext.get(id)==null)
    {
        // first time create the iframe
        var frame = document.createElement('iframe');
        frame.id = id;
        frame.name = id;
        frame.src = cachebuster + "?src=" + escape(url);
        frame.className = 'x-hidden';
        document.body.appendChild(frame);
        // give browser time to append new frame before reloading
        forceReload.defer(200, this, [cachebuster, url]);
    }
    else
    {
        parent.frames[id].location.reload();
    }
}


// --- hides page loading indicator and mask
function hidePageLoader()
{
    var loading = Ext.get('page-loading');
    var mask = Ext.get('page-loading-mask');
    if(loading && mask)
    {
        loading.ghost('b', { duration: 1.5, remove:false, easing:'easeIn' });   // remove must be "false" prevent IE SSL warning
        mask.shift({
            xy: [loading.getX(), loading.dom.offsetTop],     // (getY() returns zero in Safari on span element)
            width: loading.getWidth(),
            height: loading.getHeight(), 
            remove: true,
            duration: .75,
            opacity: .3,
            easing: 'easeOut'
        });
    }
}

// --- Set text of form status message. The status message is typically found just above the buttons
// --- at the bottom of the form. The satus message field must have id "status-msg" and
// --- class "form-status"
setFormMessage = function(message, color, loading, id)
{
    msgArea = Ext.get(id || 'status-msg');
    msgArea.setStyle("color", color);
    if(loading)
    {
        message = "<span class='form-status-loading'>" + message + "</span>";
    }
    msgArea.update(message);
    // hide message if it's empty
    msgArea.setVisibilityMode(Ext.Element.DISPLAY);
    msgArea.setVisible((message=="") ? false : true);
}

// --- validate that password has 6 characters and two digits
function validatePassword(pw)
{
    if(pw == "********")     // indicates password has not been changed
    {
        return(true);
    }
    else if(pw.length < 6)
    {
        return("Password must be at least 6 characters");
    }
    else if(hasNumbers(pw)==false)
    {
        return("Password must contain at least two digits");
    }
    else
    {
        return(true);
    }
}


// --- check to see if passed in string has at least two digits
function hasNumbers(t)
{
//    var regex = /\d/g;
    var regex = /\d{2,}/;
    return regex.test(t);
}


Ext.override(Ext.grid.GridPanel, {
// get list of selected items in the grid as comma-delimited list
    getSelectionList: function() {
        var list = "(";
    // --- build list of selected rows codes
        this.getSelectionModel().each(function(record)
        {
            list += record.data.id + ",";
        });
    // --- replace last "," with a ")"
        list = list.substr(0, list.length-1) + ")";
        return(list);
    },
// get list of items not selected in the grid as comma-delimited list
    getNotSelectedList: function() {
        var sm = this.getSelectionModel();
        var list = "(";
    // --- build list of selected disposition codes
        this.getStore().each(function(record)
        {
            if(!sm.isIdSelected(record.data.id))
            {
                list += record.data.id + ",";
            }
        });
    // --- replace last "," with a ")"
        list = list.substr(0, list.length-1) + ")";
        return(list);
    }
});


// --- Override Ext.data numeric converters to return empty string ('') rather than "0"
// --- if null is passed in. Without this change, null values in the database become "0"
// --- in form data fields which is not desired.
Ext.data.Types.INT = {
    convert: function(v){
        return v !== undefined && v !== null && v !== '' ?
            parseInt(String(v).replace(Ext.data.Types.stripRe, ''), 10) : '';
    },
    sortType: Ext.data.SortTypes.none,
    type: 'int'
}

Ext.data.Types.FLOAT = {
    convert: function(v){
        return v !== undefined && v !== null && v !== '' ?
            parseFloat(String(v).replace(Ext.data.Types.stripRe, ''), 10) : '';
    },
    sortType: Ext.data.SortTypes.none,
    type: 'float'
}

// Fix problem where x-masked / x-masked-relative classes are not removed on call to unmask (Fixed in SVN 6798 Ext 3.2+)
Ext.override(Ext.Element, {
    unmask : Ext.Element.prototype.unmask.createSequence( function(){
        this.removeClass(['x-masked', 'x-masked-relative']);
      })
});

// In standards mode the body element cannot be scrolled with scrollTop/scrollLeft, instead
// we must use window.scrollTo()
Ext.override(Ext.lib.Scroll, {
    setAttr : function(attr, val, unit) {
        var me = this;

        if(attr == 'scroll'){
            if(me.el == Ext.getBody().dom)
            {
                window.scrollTo(val[0], val[1]);
            }
            else
            {
                me.el.scrollLeft = val[0];
                me.el.scrollTop = val[1];
            }
        }else{
            superclass.setAttr.call(me, attr, val, unit);
        }
    }
});


/*Ext.override(Ext.Element, {
    xclearOpacity : function(){
        var style = this.dom.style;
        if(Ext.isIE){
//            if(!Ext.isEmpty(style.filter)){
//                style.filter = style.filter.replace(opacityRe, '').replace(trimRe, '');
//            }
            style.cssText = style.cssText.replace('FILTER: ;', '');
//            style.zoom=0;
        }
        else
        {
            style.opacity = style['-moz-opacity'] = style['-khtml-opacity'] = '';
        }
        return this;
    }
});*/

// Fix bug where tooltip anchor arrow is offset to the right and down the second time the
// tooltip is displayed for a given element
Ext.override(Ext.ToolTip, {
    afterRender : function(){
        Ext.ToolTip.superclass.afterRender.call(this);
        this.anchorEl.setStyle('z-index', this.el.getZIndex() + 1);
    }
});

// --- Override the default container to fix the form label spacing in IE6. This works in conjuntion with
// --- the .x-form-item label style modification in our .css
Ext.override(Ext.Container, {
    defaults: { labelPad: (Ext.isIE6) ? 2 : 5 }
});

// bigPuff is twice as large as puff. It also fixes several problems with IE (see comments)
Ext.Element.addMethods({
    bigPuff : function(o){
        var me = this,
            dom = me.dom,
            st = dom.style,
            width,
            height,
            r;

        me.queueFx(o, function(){
            width = Ext.fly(dom).getWidth();
            height = Ext.fly(dom).getHeight();
            Ext.fly(dom).clearOpacity();
            Ext.fly(dom).show();

            // restore values after effect
            r = Ext.fly(dom).getFxRestore();                   
            
            function after(){
                o.useDisplay ? Ext.fly(dom).setDisplayed(FALSE) : Ext.fly(dom).hide();                  
                Ext.fly(dom).clearOpacity();  
                Ext.fly(dom).setPositioning(r.pos);
                st.width = r.width;
                st.height = r.height;
                st.fontSize = '';
                Ext.fly(dom).afterFx(o);
            }   

            arguments.callee.anim = Ext.fly(dom).fxanim({
                // IE requires width and height to have "from" because the core FX code will calculate "from" incorrectly
                // because it doesn't handle the IE box model correctly
                    width : {from : Ext.fly(dom).adjustWidth(width), to : Ext.fly(dom).adjustWidth(width * 4)},
                    height : {from : Ext.fly(dom).adjustHeight(height), to : Ext.fly(dom).adjustHeight(height * 4)},
                    points : {by : [-width * 1.5, -height * 1.5]},
                    opacity : {to : 0}
                // fontSize causes problems in IE with "backIn" easing. The problem is the font size % becomes negative
                // and IE cannot handle this
//                    fontSize: {to : 200, unit: "%"}
                },
                o,
                'motion',
                .5,
                'easeOut',
                 after);
        });
        return me;
    }
});


// --- Override grid view 
Ext.override(Ext.grid.GridView, {
    // render editors into document body instead of grid scroller. This allows the textarea
    // editors and row editors to expand outside of the bottom of the grid if needed 
    getEditorParent: function() {
        return document.body;
    },
    // maintain current scroll position when grid loads (default behavior is to scroll back
    // to the top)
    onLoad: Ext.emptyFn,
    listeners: {
        beforerefresh: function(v) {
           v.scrollTop = v.scroller.dom.scrollTop;
           v.scrollHeight = v.scroller.dom.scrollHeight;
        },
        refresh: function(v) {
           v.scroller.dom.scrollTop = v.scrollTop + (v.scrollTop == 0 ? 0 : v.scroller.dom.scrollHeight - v.scrollHeight);
        }
    }
});


// Enhanced version of Checkbox selection model (see function comments)
Ext.grid.CheckboxSelectionModelFS = Ext.extend(Ext.grid.CheckboxSelectionModel, {
    
    // select rows by the record ID
    selectRowsById : function(IDs) {
        var ds = this.grid.getStore();
        var records = new Array();
        Ext.each(IDs, function(item) {
            records.push(ds.getById(item));
        });
        this.clearSelections();
        this.selectRecords(records);
    },
    
    initEvents : function() {
        Ext.grid.CheckboxSelectionModelFS.superclass.initEvents.call(this);     // call base class

        this.on('beforerowselect', function(sm, row){
        // When user clicks on a grid row, add that grid row to the current selection
        // Default behavior is to clear current selection and then select the clicked row
            sm.suspendEvents();         // prevent recursion
            sm.selectRow(row, true);    // add selected row to current selection
            sm.resumeEvents();
            sm.fireEvent('selectionchange', sm);    // fire selection change event
            return(false);              // prevent default behavior
        }, this);

        this.on('selectionchange', function(sm){
        // If all rows are selected, check box in grid header, otherwise clear check box in grid header
        // (for some reason this functionality is not built into the selection model by default)
            var hd = Ext.fly(sm.grid.getView().innerHd).child('div.x-grid3-hd-checker');
            if (sm.getCount() < sm.grid.getStore().getCount() || sm.grid.getStore().getCount()==0)
            {
                hd.removeClass('x-grid3-hd-checker-on');    // uncheck the box
            }
            else
            {
                hd.addClass('x-grid3-hd-checker-on');       // check the box
            }
        }, this);
    }
});


// --- Override TextArea to limit text to maxLength
Ext.override(Ext.form.TextArea, {
    enableKeyEvents: true,  // we need to trap key events to check for max length
    listeners: {
        'keypress': function(field, event) {
            var val = field.getValue();
            if(event.keyCode!=event.BACKSPACE && event.keyCode!=event.DELETE && !event.isNavKeyPress()){
                if (val.length >= field.maxLength ){
                    event.stopEvent();
                }
            }
        }
    }
});


// --- Make a version of textarea where the enter key is a "special key" (sumbits changes to editor grid) and
// --- pressing shift+enter adds a newline
Ext.form.TextAreaMLG = Ext.extend(Ext.form.TextArea, {
    fireKey : function(e){
        if(e.isSpecialKey() && !(e.getKey() == e.ENTER && e.shiftKey)) {
            this.fireEvent("specialkey", this, e);
        }
    }
});
Ext.ComponentMgr.registerType('textareamlg', Ext.form.TextAreaMLG);


// --- Define local and remote versions of the combobox to encapsulate
// --- the parameters we use over and over into the class.
Ext.form.RemoteComboBox = Ext.extend(Ext.form.ComboBox, {
    mode:'remote',
    pageSize: 20,
    queryDelay: 10,
    minChars: 2,
    typeAhead:false,
    triggerAction:'all',
    selectOnFocus:true,
    forceSelection: false,
    initEvents : function()
    {
    // --- Call base class init function
        Ext.form.LocalComboBox.superclass.initEvents.call(this);
    // --- Change behavior of tab key so that highlighted item in drop-down list is selected when
    // --- list collapses. This was the behavior in v3.1.0 but in v3.1.1 the tab key collapses the
    // --- list and does not select the highlighted item.
        this.keyNav.tab = function() {
            this.onViewClick(false);
            return true;
         };
    }
});
Ext.form.LocalComboBox = Ext.extend(Ext.form.ComboBox, {
    mode:'local',
    typeAhead:false,        // must be false to prevent wrong item being selected when user types quickly
    triggerAction:'all',    // show all items in the list when trigger is clicked (list not filtered by current text)
    selectOnFocus: true,
    forceSelection: false,
    // --- When user types a value into a combobox Ext creates a filter on the 
    // --- data store attached to the combobox to filter the values displayed to the user.
    // --- Later, if setValue() is called, this filter is still set in the data store and
    // --- the combo box may not display the text associated with the value.
    // --- To work around this, clear the data store filter
    setValue: function(v)
    {
        this.store.clearFilter();
    // --- Then call base class setValue() function
        Ext.form.LocalComboBox.superclass.setValue.call(this, v);
    },
    // --- findRecord is used by the combo box when setting the value by id. This function
    // --- looks up the record in the data store to get the matching display text. By overriding
    // --- this function to use a separate data store (storeWithObsoletes), the combo box can
    // --- find matching display text for obsolete items when displaying existing records.
    // --- The standard data store (store) will be used to display the drop down list and filter
    // --- type-ahead so the user will not be able to choose an obsolete item when creating a new record.
    findRecord: function(prop, value)
    {
        var record;
        var store = (this.storeWithObsoletes) ? this.storeWithObsoletes : this.store;
        if(store.getCount() > 0){
            store.each(function(r) {
                if(r.data[prop] == value){
                    record = r;
                    return false;
                }
            });
        }
        return record;
    },
    // --- if hideObsoletes is defined, create storeWithObsoletes for use in our override of findRecord()
    initComponent: function()
    {
        if(this.hideObsoletes)
        {
        // --- copy data store into storeWithObsoletes
            var records = this.store.getRange();
            this.storeWithObsoletes = new Ext.data.Store( { recordType: this.store.recordType });
            this.storeWithObsoletes.add(records);
        // --- remove obsolete records from original store
            this.store.each(function(r) {
                if(r.data.obsolete) {
                    this.store.remove(r);
                }
            }, this);
        }
    // --- Call base class init function
        Ext.form.LocalComboBox.superclass.initComponent.call(this);
    },
    initEvents : function()
    {
    // --- Call base class init function
        Ext.form.LocalComboBox.superclass.initEvents.call(this);
    // --- Change behavior of tab key so that highlighted item in drop-down list is selected when
    // --- list collapses. This was the behavior in v3.1.0 but in v3.1.1 the tab key collapses the
    // --- list and does not select the highlighted item.
        this.keyNav.tab = function() {
            this.onViewClick(false);
            return true;
         };
    }
});

Ext.ComponentMgr.registerType('remotecombobox', Ext.form.RemoteComboBox);
Ext.ComponentMgr.registerType('localcombobox', Ext.form.LocalComboBox);


// --- A ComboBox with a secondary trigger button that clears the contents of the ComboBox
Ext.form.ClearableComboBox = Ext.extend(Ext.form.ComboBox, {
    initComponent: function() {
        this.triggerConfig = {
            tag:'span', cls:'x-form-twin-triggers', cn:[
            {tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger"},
            {tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-clear-trigger"}
        ]};
        Ext.form.ClearableComboBox.superclass.initComponent.call(this);
    },
    onTrigger2Click : function()
    {
        this.collapse();
        this.reset();                       // clear contents of combobox
        this.fireEvent('cleared');          // send notification that contents have been cleared
    },
    initEvents : function()
    {
    // --- Call base class init function
        Ext.form.LocalComboBox.superclass.initEvents.call(this);
    // --- Change behavior of tab key so that highlighted item in drop-down list is selected when
    // --- list collapses. This was the behavior in v3.1.0 but in v3.1.1 the tab key collapses the
    // --- list and does not select the highlighted item.
        this.keyNav.tab = function() {
            this.onViewClick(false);
            return true;
         };
    },    
    getTrigger: Ext.form.TwinTriggerField.prototype.getTrigger,
    initTrigger: Ext.form.TwinTriggerField.prototype.initTrigger,
    onTrigger1Click: Ext.form.ComboBox.prototype.onTriggerClick,
    trigger1Class: Ext.form.ComboBox.prototype.triggerClass
});


// --- Define local and remote versions of the clearable combobox to encapsulate
// --- the parameters we use over and over into the class.
Ext.form.RemoteClearableComboBox = Ext.extend(Ext.form.ClearableComboBox, {
    mode:'remote',
    pageSize: 20,
    queryDelay: 10,
    minChars: 2,
    typeAhead:false,
    triggerAction:'all',
    selectOnFocus:true,
    forceSelection: false
});
Ext.form.LocalClearableComboBox = Ext.extend(Ext.form.ClearableComboBox, {
    mode:'local',
    typeAhead:false,
    triggerAction:'all',
    selectOnFocus:true,
    forceSelection: false
});

Ext.ComponentMgr.registerType('clearablecombobox', Ext.form.ClearableComboBox);
Ext.ComponentMgr.registerType('localclearablecombobox', Ext.form.LocalClearableComboBox);
Ext.ComponentMgr.registerType('remoteclearablecombobox', Ext.form.RemoteClearableComboBox);
