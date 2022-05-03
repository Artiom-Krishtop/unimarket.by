this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_ui_panel_iconpanel,landing_ui_field_image) {
	'use strict';

	/**
	 * @memberOf BX.Landing.UI.Field
	 */

	var Icon = /*#__PURE__*/function (_Image) {
	  babelHelpers.inherits(Icon, _Image);

	  function Icon(data) {
	    var _this;

	    babelHelpers.classCallCheck(this, Icon);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Icon).call(this, data));
	    _this.uploadButton.layout.innerText = BX.Landing.Loc.getMessage("LANDING_ICONS_FIELD_BUTTON_REPLACE");
	    _this.editButton.layout.hidden = true;
	    _this.clearButton.layout.hidden = true;

	    _this.dropzone.removeEventListener("dragover", _this.onDragOver);

	    _this.dropzone.removeEventListener("dragleave", _this.onDragLeave);

	    _this.dropzone.removeEventListener("drop", _this.onDrop);

	    _this.preview.removeEventListener("dragenter", _this.onImageDragEnter);

	    landing_ui_panel_iconpanel.IconPanel.getLibraries().then(function (libraries) {
	      if (libraries.length === 0) {
	        this.uploadButton.disable();
	      }
	    }.bind(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }

	  babelHelpers.createClass(Icon, [{
	    key: "onUploadClick",
	    value: function onUploadClick(event) {
	      event.preventDefault();
	      landing_ui_panel_iconpanel.IconPanel.getInstance().show().then(function (iconClassName) {
	        this.setValue({
	          type: "icon",
	          classList: iconClassName.split(" ")
	        });
	      }.bind(this));
	    }
	  }, {
	    key: "isChanged",
	    value: function isChanged() {
	      return this.getValue().classList.some(function (className) {
	        return this.content.classList.indexOf(className) === -1;
	      }, this);
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var classList = this.classList;

	      if (this.selector) {
	        var selectorClassname = this.selector.split("@")[0].replace(".", "");
	        classList = main_core.Runtime.clone(this.classList).concat([selectorClassname]);
	        classList = BX.Landing.Utils.arrayUnique(classList);
	      }

	      return {
	        type: "icon",
	        src: "",
	        id: -1,
	        alt: "",
	        classList: classList,
	        url: Object.assign({}, this.url.getValue(), {
	          enabled: this.urlCheckbox.checked
	        })
	      };
	    }
	  }, {
	    key: "reset",
	    value: function reset() {
	      this.setValue({
	        type: "icon",
	        src: "",
	        id: -1,
	        alt: "",
	        classList: [],
	        url: ''
	      });
	    }
	  }]);
	  return Icon;
	}(landing_ui_field_image.Image);

	exports.Icon = Icon;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX,BX.Landing.UI.Panel,BX.Landing.UI.Field));
//# sourceMappingURL=icon.bundle.js.map
