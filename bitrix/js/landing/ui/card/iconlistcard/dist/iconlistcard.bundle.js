this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_ui_card_basecard,landing_loc) {
	'use strict';

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"", "", "\"></span>"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-card landing-ui-card-icon\">\n\t\t\t\t<span class=\"", "\"></span>\n\t\t\t</div>\n\t\t"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-card-icons-container\"></div>"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"", "\"></span>"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-card-preview --hide\">\n\t\t\t\t\t<div class=\"landing-ui-card-preview-icon\"></div>\n\t\t\t\t\t<div class=\"landing-ui-card-preview-additional\">\n\t\t\t\t\t\t<div class=\"landing-ui-card-preview-title\">", "</div>\n\t\t\t\t\t\t<div class=\"landing-ui-card-preview-options\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-card landing-ui-card-icons\">\n\t\t\t\t\t<div class=\"landing-ui-card-header-wrapper\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"landing-ui-card-body-wrapper\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	/**
	 * @memberOf BX.Landing.UI.Card
	 */

	var IconListCard = /*#__PURE__*/function (_BaseCard) {
	  babelHelpers.inherits(IconListCard, _BaseCard);

	  function IconListCard(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, IconListCard);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(IconListCard).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Card.IconListCard');

	    _this.title = '';
	    _this.items = new Map();
	    _this.activeIcon = null;
	    return _this;
	  }

	  babelHelpers.createClass(IconListCard, [{
	    key: "getLayout",
	    value: function getLayout() {
	      var _this2 = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject(), _this2.getHeader(), _this2.getPreview(), _this2.getBody());
	      });
	    }
	  }, {
	    key: "getPreview",
	    value: function getPreview() {
	      return this.cache.remember('preview', function () {
	        return main_core.Tag.render(_templateObject2(), landing_loc.Loc.getMessage('LANDING_ICONS_CHANGE_STYLE'));
	      });
	    }
	  }, {
	    key: "getPreviewIcon",
	    value: function getPreviewIcon() {
	      return this.getPreview().querySelector('.landing-ui-card-preview-icon');
	    }
	  }, {
	    key: "getPreviewOptions",
	    value: function getPreviewOptions() {
	      return this.getPreview().querySelector('.landing-ui-card-preview-options');
	    }
	  }, {
	    key: "setPreviewIcon",
	    value: function setPreviewIcon(className) {
	      var icon = main_core.Tag.render(_templateObject3(), className);
	      main_core.Dom.clean(this.getPreviewIcon());
	      main_core.Dom.append(icon, this.getPreviewIcon());
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(item, additional) {
	      var _this3 = this;

	      if (this.getBody().childElementCount === 0) {
	        this.itemsContainer = main_core.Tag.render(_templateObject4());
	        main_core.Dom.append(this.itemsContainer, this.getBody());
	      }

	      var icon = main_core.Tag.render(_templateObject5(), item);
	      main_core.Event.bind(icon, 'click', this.onItemClick.bind(this, icon, additional));
	      main_core.Dom.append(icon, this.itemsContainer); // todo: need?
	      // duplicate control

	      var styles = getComputedStyle(icon.querySelector('span'), ':before');
	      requestAnimationFrame(function () {
	        var content = styles.getPropertyValue('content');

	        if (_this3.items.has(content)) {
	          icon.hidden = true;
	        } else {
	          _this3.items.set(content, true);
	        }
	      });
	    }
	  }, {
	    key: "onItemClick",
	    value: function onItemClick(item, additional) {
	      var prevActive = this.getBody().querySelector('.landing-ui-card-icon.--active');

	      if (prevActive) {
	        main_core.Dom.removeClass(prevActive, '--active');
	      }

	      main_core.Dom.addClass(item, '--active');
	      this.activeIcon = item.firstElementChild.className;

	      if (main_core.Type.isObject(additional)) {
	        this.setPreviewIcon(additional.defaultOption);
	        this.setPreviewOptions(additional.options, additional.defaultOption);
	      } else {
	        this.setPreviewIcon(this.activeIcon);
	        this.setPreviewOptions([this.activeIcon], this.activeIcon);
	      }

	      main_core.Dom.removeClass(this.getPreview(), '--hide');
	    }
	  }, {
	    key: "getActiveIcon",
	    value: function getActiveIcon() {
	      return this.activeIcon;
	    }
	  }, {
	    key: "setPreviewOptions",
	    value: function setPreviewOptions(options, defaultOption) {
	      var _this4 = this;

	      var optionsLayout = this.getPreviewOptions();
	      main_core.Dom.clean(optionsLayout);
	      options.forEach(function (option) {
	        var isActive = option === defaultOption ? ' --active' : '';
	        var optionLayout = main_core.Tag.render(_templateObject6(), option, isActive);
	        main_core.Event.bind(optionLayout, 'click', _this4.onOptionClick.bind(_this4, option));
	        main_core.Dom.append(optionLayout, optionsLayout);
	      });
	    }
	  }, {
	    key: "onOptionClick",
	    value: function onOptionClick(option) {
	      this.activeIcon = option;
	      this.setPreviewIcon(option);
	      this.getPreviewOptions().querySelectorAll('span').forEach(function (optionItem) {
	        main_core.Dom.removeClass(optionItem, '--active');

	        if (main_core.Dom.hasClass(optionItem, option)) {
	          main_core.Dom.addClass(optionItem, '--active');
	        }
	      });
	    }
	  }]);
	  return IconListCard;
	}(landing_ui_card_basecard.BaseCard);

	exports.IconListCard = IconListCard;

}((this.BX.Landing.UI.Card = this.BX.Landing.UI.Card || {}),BX,BX.Landing.UI.Card,BX.Landing));
//# sourceMappingURL=iconlistcard.bundle.js.map
