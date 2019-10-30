importOptions.ExcelUploadForm = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        layout: 'form'
        , url: importOptions.config.connector_url
        , baseParams: {
            action: 'mgr/options/upload'
        }
        , id: 'importoptions-options-upload-form'
        , keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
        , items: this.getFields(config)
        , listeners: {
            success: {
                fn: function () {
                    location.reload();
                }, scope: this
            }
        }
    });
    importOptions.ExcelUploadForm.superclass.constructor.call(this, config);
};
Ext.extend(importOptions.ExcelUploadForm, MODx.FormPanel, {
    getFields: function (config) {
        return [{
            layout: 'column'
            ,items: [
                {
                    name: 'file'
                    , xtype: 'modx-combo-browser'
                    , hideFiles: true
                    , source:  MODx.config.default_media_source
                    , id: 'importoptions-options-upload-input'
                    , emptyText: _('importoptions_upload_options')

                }, {
                    xtype: 'button'
                    , cls: 'primary-button'
                    , text: _('importoptions_upload_btn')
                    , id: 'importoptions-options-upload'
                    , listeners: {
                        click: {
                            fn: function () {
                                this.submit();
                            }, scope: this
                        },
                    }
                }
            ],
        }];
    },
});
Ext.reg('importoptions-options-excel-upload-form', importOptions.ExcelUploadForm);



importOptions.panel.Home = function (config) {
    config = config || {};
    Ext.apply(config, {
        baseCls: 'modx-formpanel',
        layout: 'anchor',
        hideMode: 'offsets',
        items: [{
            html: '<h2>' + _('importoptions') + '</h2>',
            cls: '',
            style: {margin: '15px 0'}
        }, {
            xtype: 'modx-tabs',
            defaults: {border: false, autoHeight: true},
            border: true,
            hideMode: 'offsets',
            items: [{
                title: _('importoptions_import'),
                layout: 'anchor',
                items: [{
                    html: _('importoptions_intro_msg'),
                    cls: 'panel-desc',
                }, {
                    xtype: 'importoptions-options-excel-upload-form'
                    , id: 'importoptions-options-excel-upload-form',
                    cls: 'main-wrapper',
                }]
            }],

        },]
    });
    importOptions.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(importOptions.panel.Home, MODx.Panel);
Ext.reg('importoptions-panel-home', importOptions.panel.Home);

