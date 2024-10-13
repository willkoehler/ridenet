/**
 * @class Ext.ux.ColorPicker
 * @extends Ext.BoxComponent
 * This is a color picker.
 * @license: LGPLv3
 * @author: Amon
 * @author: Jerome Carbou (extjs id: jcarbou)
 * @constructor
 * Creates a new ColorPicker
 * @param {Object} config Configuration options
 * @version 1.1.2
 */

Ext.namespace( 'Ext.ux' );
/**
 *
 */
Ext.ux.ColorPicker = Ext.extend( Ext.BoxComponent, {
    opacity:true,

    /**
     *
     */
    initComponent: function() {
        this.applyDefaultsCP();
        Ext.ux.ColorPicker.superclass.initComponent.apply( this, arguments );
    },
    /**
     *
     */
    onRender: function() {
        Ext.ux.ColorPicker.superclass.onRender.apply( this, arguments );
        // check if container, self-container or renderTo exists
        this.body = this.body || ( this.container || ( this.renderTo || Ext.DomHelper.append( Ext.getBody(), {}, true ) ) );
        if( !this.el ) {
            this.el = this.body;
            if( this.cls ) { Ext.get( this.el ).addClass( this.cls ); }
        }
        this.body.setSize(328,this.opacity ? 211 : 191);
        // render this component
        this.renderComponent();
    },
    /**
     *
     */
    applyDefaultsCP: function() {
        Ext.apply( this, {
            'cls': 'x-cp-mainpanel',
            'resizable': this.resizable || false,
            'HSV': {
                h: 0,
                s: 0,
                v: 0
            },
            updateMode: null
        });
    },
    /**
     *
     */
    renderComponent: function() {
        // create RGB Picker
        this.rgbEl =  Ext.DomHelper.append( this.body, {
            'id': this.cpGetId( 'rgb' ),
            'cls': 'x-cp-rgbpicker'
        },true);
        // Create HUE Picker
        this.hueEl = Ext.DomHelper.append( this.body, {
            'id': this.cpGetId( 'hue' ),
            'cls': 'x-cp-huepicker'
        },true);
        // Create Opacity Picker
        if (this.opacity) {
            this.opacityEl = Ext.DomHelper.append( this.body, {
                'id': this.cpGetId( 'opacity' ),
                'cls': 'x-cp-opacitypicker'
            },true);
        }
        // Initialize HUE Picker
        this.hueSliderEl = Ext.DomHelper.append( this.hueEl, { 'cls': 'x-cp-slider',id:this.cpGetId( 'hueSlider' ) },true);
        // when mouse buttons is pressed down move rgbSlider within rgbEl to pick new color (WCK)
        this.hueEl.on('mousedown', function(e) {
            this.moveHuePicker(e);
            Ext.getBody().on('mousemove', this.moveHuePicker, this);
        }, this);
        // when mouse button is released stop moving rgbSlider (WCK)
        Ext.getBody().on('mouseup', function(e) {
            Ext.getBody().un('mousemove', this.moveHuePicker, this);
        }, this);
        // initialize start position
        Ext.get( this.hueSliderEl ).moveTo( this.hueEl.getLeft() - 3, this.hueEl.getTop() - 7 );

        // Initialize RGB Picker
        this.rgbSliderEl = Ext.DomHelper.append( this.rgbEl, { 'cls': 'x-cp-slider',id:this.cpGetId( 'rgbSlider' ) },true);
        // when mouse buttons is pressed down move rgbSlider within rgbEl to pick new color (WCK)
        this.rgbEl.on('mousedown', function(e) {
            this.moveRGBPicker(e);
            Ext.getBody().on('mousemove', this.moveRGBPicker, this);
        }, this);
        // when mouse button is released stop moving rgbSlider (WCK)
        Ext.getBody().on('mouseup', function(e) {
            Ext.getBody().un('mousemove', this.moveRGBPicker, this);
        }, this);
        // initialize start position
        this.rgbSliderEl.moveTo( this.rgbEl.getLeft() - 7, this.rgbEl.getTop() - 7 );
        // Create color divs and Form elements

        if (this.opacity) {
            // Initialize Opacity Picker DD
            this.opacitySliderEl = Ext.DomHelper.append( this.body, { 'cls': 'x-cp-slider' ,id:this.cpGetId( 'opacitySlider' ) },true);
            this.opacityDD = new Ext.dd.DD( this.opacitySliderEl.id, 'opacityPicker' );
            this.opacityDD.constrainTo( this.opacityEl.id, {'top':-3,'right':-7,'bottom':-3,'left':-7} );
            this.opacityDD.onDrag = this.moveOpacityPicker.createDelegate( this );
            // initialize onclick on the rgb picker
            this.opacityEl.on( 'mousedown', this.clickOpacityPicker.createDelegate( this ) );
            // initialize start position
            this.opacitySliderEl.moveTo( this.opacityEl.getRight() - 7, this.opacityEl.getTop() - 3 );
            this.alpha=256;
        }

        Ext.DomHelper.append( this.body, {
            'id': this.cpGetId( 'fCont' ),
            'cls': 'x-cp-formcontainer'
        }, true );

        this.formPanel = new Ext.form.FormPanel({
            renderTo:this.cpGetId( 'fCont' ),
            height: this.opacity ? 202 : 182,
            width:110,
            frame: true,
            header:false,
            labelAlign: 'left',
            labelWidth: 37,
            forceLayout: true,
            items: [{
                layout: 'column',
                defaults : {
                    columnWidth: .5,
                    layout: 'form',
                    labelWidth: 10,
                    defaultType: 'numberfield',
                    defaults: {
                        width: 30,
                        value: 0,
                        minValue: 0,
                        maxValue: 255,
                        allowBlank: false,
                        labelSeparator: ''
                    }
                },
                items: [{
                    items: [{
                        fieldLabel: 'R',
                        id: this.cpGetId( 'iRed' )
                    },{
                        fieldLabel: 'G',
                        id: this.cpGetId( 'iGreen' )
                    },{
                        fieldLabel: 'B',
                        id: this.cpGetId( 'iBlue' )
                    }]
                },{
                    style:{
                        paddingLeft:'3px'
                    },
                    items: [{
                        fieldLabel: 'H',
                        maxValue: 360,
                        id: this.cpGetId( 'iHue' )
                    },{
                        fieldLabel: 'S',
                        id: this.cpGetId( 'iSat' )
                    },{
                        fieldLabel: 'V',
                        id: this.cpGetId( 'iVal' )
                    }]
                }]
            },{
                layout: 'form',
                labelAlign: 'left',
                items: [{
                    xtype:'textfield',
                    width: 55,
                    allowBlank: false,
                    fieldLabel: 'HTML',
                    labelSeparator: '',
                    id: this.cpGetId( 'iHexa' ),
                    value: '000000'
                },{
                    hideLabel:true,
                    width:116,
                    layout:'form',
                    labelWidth:62,
                    hidden:!this.opacity,
                    items:[{
                        xtype:'numberfield',
                        fieldLabel: 'Opacity',
                        id: this.cpGetId( 'iOpacity' ),
                        width: 30,
                        value: 100,
                        minValue: 0,
                        maxValue: 100,
                        allowBlank: false,
                        labelSeparator: ''
                    }]
                },{
                    hideLabel: true,
                    anchor: '100%',
                    height: 44,
                    width:98,
                    layout: 'table',
                    layoutConfig: {
                        columns: '2'
                    },
                    id: this.cpGetId('cColorCt'),
                    cls:'x-cp-preview',
                    items: [{
                        border:true,
                        width: 75,
                        height: 42,
                        rowspan:2,
                        html:[{
                            id:this.cpGetId('cColor'),
                            cls:'x-cp-previewbox'
                        },{
                            id:this.cpGetId('cColorOpacity'),
                            cls:'x-cp-previewbox'
                        }]
                    },{
                        xtype: 'button',
                        iconCls: 'x-cp-webSafe',
                        id:this.cpGetId( 'cWebSafe' ),
                        handler: this.updateFromBox.createDelegate(this)
                    },{
                        xtype: 'button',
                        iconCls: 'x-cp-inverse',
                        id:this.cpGetId( 'cInverse' ),
                        handler: this.updateFromBox.createDelegate(this)
                    }]
                },{
                    xtype:'container',
                    layout: 'column',
                    anchor: '100%',
                    items: [{
                        xtype:'button',
                        width: 95,
                        hideLabel:true,
                        text: 'Close',
                        handler: this.selectColor,
                        scope: this,
                        style:{
                            marginTop:'2px'
                        }
                    }]
                }]
            }]
        });

        this.redCmp = Ext.getCmp( this.cpGetId( 'iRed' ) );
        this.greenCmp = Ext.getCmp( this.cpGetId( 'iGreen' ) );
        this.blueCmp = Ext.getCmp( this.cpGetId( 'iBlue' ) );
        this.hueCmp = Ext.getCmp( this.cpGetId( 'iHue' ) );
        this.satCmp = Ext.getCmp( this.cpGetId( 'iSat' ) );
        this.valCmp = Ext.getCmp( this.cpGetId( 'iVal' ) );
        this.hexaCmp = Ext.getCmp( this.cpGetId( 'iHexa' ) );

        this.redCmp.on( 'change', this.updateFromIRGB.createDelegate( this ) );
        this.greenCmp.on( 'change', this.updateFromIRGB.createDelegate( this ) );
        this.blueCmp.on( 'change', this.updateFromIRGB.createDelegate( this ) );
        this.hueCmp.on( 'change', this.updateFromIHSV.createDelegate( this ) );
        this.satCmp.on( 'change', this.updateFromIHSV.createDelegate( this ) );
        this.valCmp.on( 'change', this.updateFromIHSV.createDelegate( this ) );
        this.hexaCmp.on( 'change', this.updateFromIHexa.createDelegate( this ) );

        this.previewEl = Ext.get( this.cpGetId( 'cColor' ) );
        this.opacityPreviewEl = Ext.get( this.cpGetId( 'cColorOpacity' ) );
        this.inverseButton = Ext.getCmp(this.cpGetId( 'cInverse' ));
        this.webSafeButton = Ext.getCmp(this.cpGetId( 'cWebSafe' ));

        Ext.DomHelper.append( this.body, {'tag':'br','cls':'x-cp-clearfloat'});
    },

    onDestroy : function(){
        Ext.ux.ColorPicker.superclass.onDestroy.apply( this, arguments );
        Ext.destroyMembers(this,'formPanel','inverseButton','webSafeButton',
        'rgbEl','hueEl','opacityEl','rgbSliderEl','hueSliderEl','opacitySliderEl',
        'previewEl','opacityPreviewEl','redCmp','greenCmp','blueCmp','hueCmp','satCmp','valCmp','hexaCmp');
    },

    /**
     *
     */
    cpGetId: function( postfix ) {
        return this.getId() + '__' + ( postfix || 'cp' );
    },
    /**
     *
     */
    updateRGBPosition: function( x, y ) {
        this.updateMode = 'click';
        x = x < 0 ? 0 : x;
        x = x > 181 ? 181 : x;
        y = y < 0 ? 0 : y;
        y = y > 181 ? 181 : y;
        this.HSV.s = this.getSaturation( x );
        this.HSV.v = this.getValue( y );
        this.rgbSliderEl.moveTo( this.rgbEl.getLeft() + x - 7, this.rgbEl.getTop() + y - 7/*, ( this.animateMove || true )*/ );
        this.updateColor();
    },
    /**
     *
     */
    updateHUEPosition: function( y ) {
        this.updateMode = 'click';
        y = y < 1 ? 1 : y;
        y = y > 181 ? 181 : y;
        this.HSV.h = Math.round( 360 / 181 * ( 181 - y ) );
        this.hueSliderEl.moveTo( this.hueSliderEl.getLeft(), this.hueEl.getTop() + y - 7/*, ( this.animateMove || true )*/ );
        this.updateRGBPicker( this.HSV.h );
        this.updateColor();
    },
    /**
     *
     */
    updateOpacityPosition: function( x ) {
        this.updateMode = 'click';
        x = x < 1 ? 1 : x;
        x = x > 181 ? 181 : x;
        this.alpha = Math.round( x/181*256);
        this.opacitySliderEl.moveTo( this.opacityEl.getLeft() + x - 7, this.opacitySliderEl.getTop()/*, ( this.animateMove || true )*/ );
        this.updateColor();
    },
    /**
     *
     */
    moveRGBPicker: function( event ) {
        this.updateRGBPosition( event.xy[0] - this.rgbEl.getLeft() , event.xy[1] - this.rgbEl.getTop() );
        event.stopEvent();
    },
    /**
     *
     */
    moveHuePicker: function( event ) {
        this.updateHUEPosition( event.xy[1] - this.hueEl.getTop() );
        event.stopEvent();
    },
    /**
     *
     */
    clickOpacityPicker: function( event, element ) {
        this.updateOpacityPosition( event.xy[0] - this.opacityEl.getLeft() );
    },
    /**
     *
     */
    moveOpacityPicker: function( event ) {
        this.opacityDD.constrainTo( this.opacityEl.id, {'top':-3,'right':-7,'bottom':-3,'left':-7} );
        this.updateOpacityPosition( this.opacitySliderEl.getLeft() - this.opacityEl.getLeft() + 7 );
    },
    /**
     *
     */
    updateRGBPicker: function( newValue ) {
        this.updateMode = 'click';
        this.rgbEl.setStyle({ 'background-color': '#' + this.rgbToHex( this.hsvToRgb( newValue, 1, 1 ) ) });
        this.updateColor();
    },
    /**
     *
     */
    updateColor: function() {
        var rgb = this.hsvToRgb( this.HSV.h, this.HSV.s, this.HSV.v );
        var websafe = this.websafe( rgb );
        var invert = this.invert( rgb );
        var wsInvert = this.invert( websafe );
        if( this.updateMode !== 'hexa' ) {
            this.hexaCmp.setValue( this.rgbToHex( rgb ) );
        }
        if( this.updateMode !== 'rgb' ) {
            this.redCmp.setValue( rgb[0] );
            this.greenCmp.setValue( rgb[1] );
            this.blueCmp.setValue( rgb[2] );
        }
        if( this.updateMode !== 'hsv' ) {
            this.hueCmp.setValue( Math.round( this.HSV.h ) );
            this.satCmp.setValue( Math.round( this.HSV.s * 100 ) );
            this.valCmp.setValue( Math.round( this.HSV.v * 100 ) );
        }

        var htmlC = this.rgbToHex( rgb );
        var websafeC = this.rgbToHex( websafe );
        var invertC = this.rgbToHex( invert );

        this.previewEl.setStyle({
            'background': '#' + htmlC
        });
        this.opacityPreviewEl.setStyle({
            'background': '#' + htmlC
        });
        if (this.opacity) {
            this.opacityEl.setStyle({'background-color':'#'+htmlC});
            Ext.getCmp( this.cpGetId( 'iOpacity' ) ).setValue(Math.round( this.alpha/2.56 ));
            this.opacityPreviewEl.applyStyles('opacity:'+(this.alpha/256));

        }
        this.webSafeButton.cpColor=websafeC;
        this.inverseButton.cpColor = invertC;
        this.inverseButton.setTooltip({
            title:'Inverse Color',
            text:'<div class="x-cp-tooltipcolorbox" style="background-color:#'+invertC+';color:#'+htmlC+'">#'+invertC+'</div>'
        });
        this.webSafeButton.setTooltip({
            title:'WebSafe Color',
            text:'<div class="x-cp-tooltipcolorbox" style="background-color:#'+websafeC+';color:#'+invertC+'">#'+websafeC+'</div>'
        })
        this.hueSliderEl.moveTo( this.hueSliderEl.getLeft(), this.hueEl.getTop() + this.getHPos( this.hueCmp.getValue() ) - 7/*, ( this.animateMove || true )*/ );
        this.rgbSliderEl.moveTo( this.rgbEl.getLeft() + this.getSPos( this.satCmp.getValue() / 100 ) - 7, this.hueEl.getTop() + this.getVPos( this.valCmp.getValue() / 100 ) - 7/*, ( this.animateMove || true )*/ );
        this.rgbEl.setStyle({ 'background-color': '#' + this.rgbToHex( this.hsvToRgb( this.hueCmp.getValue(), 1, 1 ) ) });
        this.fireEvent('change', htmlC);      // WCK fire real-time color change event
    },
    /**
     *
     */
    setColor: function(c) {
        var s = this.splitAphaRgbHex(c.replace('#', ''));
        if(!s) return;
        this.alpha=s.alpha;
        if (this.opacity) {
            this.opacitySliderEl.moveTo( this.opacityEl.getLeft() + (s.alpha/256*181) - 7, this.opacitySliderEl.getTop()/*, ( this.animateMove || true )*/ );
        }
        this.hexaCmp.setValue(s.rgbHex);
        this.updateFromIHexa();
    },

    splitAphaRgbHex : function(c) {
        if (this.opacity && /^[0-9a-fA-F]{8}$/.test(c)) {
            return {
                rgbHex: c.substring(2),
                alpha: this.hexToDec(c.substring(0, 2))
            }
        }
        if (!/^[0-9a-fA-F]{6}$/.test(c)) {
            return;
        }
        return {
            rgbHex:c,
            alpha:256
        }
    },

    /**
     *
     */
    updateFromIRGB: function( input, newValue, oldValue ) {
        this.updateMode = 'rgb';
        var temp = this.rgbToHsv( this.redCmp.getValue(), this.greenCmp.getValue(), this.blueCmp.getValue() );
        this.HSV = { h: temp[0], s:temp[1], v:temp[2]};
        this.updateColor();
    },
    /**
     *
     */
    updateFromIHSV: function( input, newValue, oldValue ) {
        this.updateMode = 'hsv';
        this.HSV = { h: this.hueCmp.getValue(), s:this.satCmp.getValue() / 100, v:this.valCmp.getValue() / 100};
        this.updateColor();
    },
    /**
     *
     */
    updateFromIHexa: function( input, newValue, oldValue ) {
        this.updateMode = 'hexa';
        var temp = this.rgbToHsv( this.hexToRgb( this.hexaCmp.getValue() ) );
        this.HSV = { h: temp[0], s:temp[1], v:temp[2]};
        this.updateColor();
    },
    /**
     *
     */
    updateFromBox: function( button, event ) {
        this.updateMode = 'click';
        var col = button.cpColor;
        col = col.replace("#", "");
        var temp = this.rgbToHsv( this.hexToRgb( col ));
        this.HSV = { h: temp[0], s:temp[1], v:temp[2]};
        this.updateColor();
    },

    selectColor: function( event, element ) {
        var c = this.previewEl.getColor( 'backgroundColor', '', '' );
        if (this.opacity) {
            c = this.decToHex(this.alpha)+c;
        }
        this.fireEvent('select', this, c);
    },
    /**
     * Convert HSV color format to RGB color format
     * @param {Integer/Array( h, s, v )} h
     * @param {Integer} s (optional)
     * @param {Integer} v (optional)
     * @return {Array}
     */
    hsvToRgb: function( h, s, v ) {
        if( h instanceof Array ) { return this.hsvToRgb.call( this, h[0], h[1], h[2] ); }
        var r, g, b, i, f, p, q, t;
        i = Math.floor( ( h / 60 ) % 6 );
        f = ( h / 60 ) - i;
        p = v * ( 1 - s );
        q = v * ( 1 - f * s );
        t = v * ( 1 - ( 1 - f ) * s );
        switch(i) {
            case 0: r=v; g=t; b=p; break;
            case 1: r=q; g=v; b=p; break;
            case 2: r=p; g=v; b=t; break;
            case 3: r=p; g=q; b=v; break;
            case 4: r=t; g=p; b=v; break;
            case 5: r=v; g=p; b=q; break;
        }
        return [this.realToDec( r ), this.realToDec( g ), this.realToDec( b )];
    },
    /**
     * Convert RGB color format to HSV color format
     * @param {Integer/Array( r, g, b )} r
     * @param {Integer} g (optional)
     * @param {Integer} b (optional)
     * @return {Array}
     */
    rgbToHsv: function( r, g, b ) {
        if( r instanceof Array ) { return this.rgbToHsv.call( this, r[0], r[1], r[2] ); }
        r = r / 255;
        g = g / 255;
        b = b / 255;
        var min, max, delta, h, s, v;
        min = Math.min( Math.min( r, g ), b );
        max = Math.max( Math.max( r, g ), b );
        delta = max - min;
        switch (max) {
            case min: h = 0; break;
            case r:   h = 60 * ( g - b ) / delta;
                      if ( g < b ) { h += 360; }
                      break;
            case g:   h = ( 60 * ( b - r ) / delta ) + 120; break;
            case b:   h = ( 60 * ( r - g ) / delta ) + 240; break;
        }
        s = ( max === 0 ) ? 0 : 1 - ( min / max );
        return [Math.round( h ), s, max];
    },
    /**
     * Convert a float to decimal
     * @param {Float} n
     * @return {Integer}
     */
    realToDec: function( n ) {
        return Math.min( 255, Math.round( n * 255 ) );
    },
    /**
     * Convert RGB color format to Hexa color format
     * @param {Integer/Array( r, g, b )} r
     * @param {Integer} g (optional)
     * @param {Integer} b (optional)
     * @return {String}
     */
    rgbToHex: function( r, g, b ) {
        if( r instanceof Array ) { return this.rgbToHex.call( this, r[0], r[1], r[2] ); }
        return this.decToHex( r ) + this.decToHex( g ) + this.decToHex( b );
    },
    /**
     * Convert an integer to hexa
     * @param {Integer} n
     * @return {String}
     */
    decToHex: function( n ) {
        var HCHARS = '0123456789ABCDEF';
        n = parseInt(n, 10);
        n = ( !isNaN( n )) ? n : 0;
        n = (n > 255 || n < 0) ? 0 : n;
        return HCHARS.charAt( ( n - n % 16 ) / 16 ) + HCHARS.charAt( n % 16 );
    },
    /**
     * Return with position of a character in this.HCHARS string
     * @private
     * @param {Char} c
     * @return {Integer}
     */
    getHCharPos: function( c ) {
        var HCHARS = '0123456789ABCDEF';
        return HCHARS.indexOf( c.toUpperCase() );
    },
    /**
     * Convert a hexa string to decimal
     * @param {String} hex
     * @return {Integer}
     */
    hexToDec: function( hex ) {
        var s = hex.split('');
        return ( ( this.getHCharPos( s[0] ) * 16 ) + this.getHCharPos( s[1] ) );
    },
    /**
     * Convert a hexa string to RGB color format
     * @param {String} hex
     * @return {Array}
     */
    hexToRgb: function( hex ) {
        return [ this.hexToDec( hex.substr(0, 2) ), this.hexToDec( hex.substr(2, 2) ), this.hexToDec( hex.substr(4, 2) ) ];
    },
    /**
     * Convert Y coordinate to HUE value
     * @private
     * @param {Integer} y
     * @return {Integer}
     */
    getHue: function( y ) {
        var hue = 360 - Math.round( ( ( 181 - y ) / 181 ) * 360 );
        return hue === 360 ? 0 : hue;
    },
    /**
     * Convert HUE value to Y coordinate
     * @private
     * @param {Integer} hue
     * @return {Integer}
     */
    getHPos: function( hue ) {
        return 181 - hue * ( 181 / 360 );
    },
    /**
     * Convert X coordinate to Saturation value
     * @private
     * @param {Integer} x
     * @return {Integer}
     */
    getSaturation: function( x ) {
        return x / 181;
    },
    /**
     * Convert Saturation value to Y coordinate
     * @private
     * @param {Integer} saturation
     * @return {Integer}
     */
    getSPos: function( saturation ) {
        return saturation * 181;
    },
    /**
     * Convert Y coordinate to Brightness value
     * @private
     * @param {Integer} y
     * @return {Integer}
     */
    getValue: function( y ) {
        return ( 181 - y ) / 181;
    },
    /**
     * Convert Brightness value to Y coordinate
     * @private
     * @param {Integer} value
     * @return {Integer}
     */
    getVPos: function( value ) {
        return 181 - ( value * 181 );
    },
    /**
     * Not documented yet
     */
    checkSafeNumber: function( v ) {
        if ( !isNaN( v ) ) {
            v = Math.min( Math.max( 0, v ), 255 );
            var i, next;
            for( i=0; i<256; i=i+51 ) {
                next = i + 51;
                if ( v>=i && v<=next ) { return ( v - i > 25 ) ? next : i; }
            }
        }
        return v;
    },
    /**
     * Not documented yet
     */
    websafe: function( r, g, b ) {
        if( r instanceof Array ) { return this.websafe.call( this, r[0], r[1], r[2] ); }
        return [this.checkSafeNumber( r ), this.checkSafeNumber( g ), this.checkSafeNumber( b )];
    },
    /**
     * Not documented yet
     */
    invert: function( r, g, b ) {
        if( r instanceof Array ) { return this.invert.call( this, r[0], r[1], r[2] ); }
        return [255-r,255-g,255-b];
    }
});
/**
 *
 */
Ext.ux.ColorDialog = Ext.extend( Ext.Window, {
    initComponent: function() {
        this.width = ( !this.width || this.width < 353 ) ? 353 : this.width;
        this.applyDefaultsCP();
        Ext.ux.ColorDialog.superclass.initComponent.apply( this, arguments );
    },
    onRender: function() {
        Ext.ux.ColorDialog.superclass.onRender.apply( this, arguments );
        this.renderComponent();
    }
});
Ext.applyIf( Ext.ux.ColorDialog.prototype, Ext.ux.ColorPicker.prototype );
/**
 *
 */
Ext.ux.ColorPanel = Ext.extend( Ext.Panel, {
    initComponent: function() {
        this.width = ( !this.width || this.width < 333 ) ? 333 : this.width;
        this.applyDefaultsCP();
        Ext.ux.ColorPanel.superclass.initComponent.apply( this, arguments );
    },
    onRender: function() {
        Ext.ux.ColorPanel.superclass.onRender.apply( this, arguments );
        this.renderComponent();
    }
});
Ext.applyIf( Ext.ux.ColorPanel.prototype, Ext.ux.ColorPicker.prototype );
/**
 * Register Color* for Lazy Rendering
 */
Ext.reg( 'colorpicker', Ext.ux.ColorPicker );
Ext.reg( 'colordialog', Ext.ux.ColorDialog );
Ext.reg( 'colorpanel', Ext.ux.ColorPanel );