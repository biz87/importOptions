var importOptions = function (config) {
    config = config || {};
    importOptions.superclass.constructor.call(this, config);
};
Ext.extend(importOptions, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('importoptions', importOptions);

importOptions = new importOptions();