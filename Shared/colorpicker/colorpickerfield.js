/**
 * @class Ext.ux.form.ColorPickerField
 * @extends Ext.form.TriggerField
 * This class makes Ext.ux.ColorPicker available as a form field.
 * @license: BSD
 * @author: Robert B. Williams (extjs id: vtswingkid)
 * @author: Tobias Uhlig (extjs id: tobiu)
 * @author: Jerome Carbou (extjs id: jcarbou)
 * @constructor
 * Creates a new ColorPickerField
 * @param {Object} config Configuration options
 * @version 1.1.2
 */

Ext.namespace("Ext.ux.menu", "Ext.ux.form");

Ext.ux.menu.ColorMenu = Ext.extend(Ext.menu.Menu, {
    enableScrolling : false,
    hideOnClick     : true,
    initComponent : function(){
        Ext.apply(this, {
            plain         : true,
            showSeparator : false,
            items: this.picker = new Ext.ux.ColorPicker(this.initialConfig)
        });
        Ext.ux.menu.ColorMenu.superclass.initComponent.call(this);
        this.relayEvents(this.picker, ['select']);
        this.on('select', this.menuHide, this);
        this.picker.on('hide', this.menuHide, this);    // (WCK) handle hide message from color picker
        if (this.handler) {
            this.on('select', this.handler, this.scope || this)
        }
    },
    menuHide: function(){
        if (this.hideOnClick) {
            this.hide(true);
        }
    }
});

Ext.ux.form.ColorPickerField = Ext.extend(Ext.form.TwinTriggerField,  {

    editMode : 'picker',

    initComponent : function(){
        this.editable = !this.hideHtmlCode;
        Ext.ux.form.ColorPickerField.superclass.initComponent.call(this);
        this.picker=-1;
        switch (this.editMode){
            case 'picker' :
                this.trigger1Class='x-form-colorfield-picker';
                this.triggerConfig = {
                    tag:'span', cls:'x-form-twin-triggers', cn:[
                    {tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger " + this.trigger1Class}
                ]};
                this.picker=0;
                break;
            case 'palette' :
                this.trigger1Class='x-form-colorfield-palette';
                this.triggerConfig = {
                    tag:'span', cls:'x-form-twin-triggers', cn:[
                    {tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger " + this.trigger1Class}
                ]};
                this.palette=0;
                break;
            default :
                this.trigger1Class='x-form-colorfield-palette';
                this.trigger2Class='x-form-colorfield-picker';
                this.triggerConfig = {
                    tag:'span', cls:'x-form-twin-triggers', cn:[
                    {tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger " + this.trigger1Class},
                    {tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger " + this.trigger2Class}
                ]};
                this.palette=0;
                this.picker=1;
        }

        this.menus =[];
        if (this.palette>=0) {
            this.menus[this.palette] = new Ext.menu.ColorMenu({
            listeners : {
                select: function(m, c) {
                    this.setValue('#' + c);
                    this.focus.defer(10, this);
                    this.fireEvent('select', this, c);  // (WCK) fire select event for the field
                }
                ,scope : this
            }
            });
            if(this.customPalette)  // (WCK) added support for custom color palette
            {
                this.menus[this.palette].palette.colors = this.customPalette;
            }
        }

        if (this.picker>=0) {
            this.menus[this.picker] = new Ext.ux.menu.ColorMenu({
                opacity:this.opacity,
                listeners : {
                    change: function(c) {
                        c = c.replace('#', '');
                        this.setValue('#'+c.toUpperCase());
                        this.fireEvent('change', this, c);  // (WCK) fire change event for the field
                    },
                    select: function(m, c) {
                        c = c.replace('#', '');
                        this.setValue('#'+c.toUpperCase());
                        this.focus.defer(10, this);
                        this.fireEvent('select', this, c);  // (WCK) fire select event for the field
                    }
                    ,scope : this
                }
            });
        }
    }

    ,setValue : function(v){
//        v = '#' + v.replace('#', ''); // (WCK) add # prefix if it's missing
        Ext.ux.form.ColorPickerField.superclass.setValue.apply(this, arguments);
        var hideStyle = this.hideHtmlCode ? 'cursor:default;' : '';
        if (v) {
            v = this.splitAphaRgbHex(v.replace('#', ''));
        }

        if (v) {
            var bg = v.rgbHex;
            var fg = /*(this.hexToDec(v.rgbHex) > 128) ? '000' : 'FFF'; //(WCK)*/this.rgbToHex(this.invert(this.hexToRgb(v.rgbHex)));
            hideStyle += 'color: #' + (this.hideHtmlCode ?  bg : fg) + ";";
            this.el.applyStyles('background: #' + bg + ';'+hideStyle);
        } else {
            this.el.applyStyles('background: #ffffff; color: #ffffff;'+hideStyle);
        }
    }

    ,onDestroy : function(){
        Ext.destroy(this.menus,this.wrap);
        Ext.ux.form.ColorPickerField.superclass.onDestroy.call(this);
    }
    ,onBlur : function(){
        Ext.ux.form.ColorPickerField.superclass.onBlur.call(this);
        this.setValue(this.getValue());
    }
    ,onTrigger1Click : function(){
        if(this.disabled){
            return;
        }
        this.menus[0].show(this.el, "tl-bl?");
        if (this.picker==0) {
            this.menus[0].picker.setColor(this.getValue());
        }
    }
    ,onTrigger2Click : function(){
        if(this.disabled){
            return;
        }
        this.menus[1].show(this.el, "tl-bl?");
        if (this.picker==1) {
            this.menus[1].picker.setColor(this.getValue());
        }
    }
    ,hexToRgb: Ext.ux.ColorPicker.prototype.hexToRgb
    ,rgbToHex: Ext.ux.ColorPicker.prototype.rgbToHex
    ,decToHex: Ext.ux.ColorPicker.prototype.decToHex
    ,hexToDec: Ext.ux.ColorPicker.prototype.hexToDec
    ,getHCharPos: Ext.ux.ColorPicker.prototype.getHCharPos
    ,invert: Ext.ux.ColorPicker.prototype.invert
    ,splitAphaRgbHex: Ext.ux.ColorPicker.prototype.splitAphaRgbHex
});
Ext.reg("colorpickerfield", Ext.ux.form.ColorPickerField);
