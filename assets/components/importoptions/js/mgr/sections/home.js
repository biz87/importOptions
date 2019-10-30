importOptions.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'importoptions-panel-home',
            renderTo: 'importoptions-panel-home-div'
        }]
    });
    importOptions.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(importOptions.page.Home, MODx.Component);
Ext.reg('importoptions-page-home', importOptions.page.Home);