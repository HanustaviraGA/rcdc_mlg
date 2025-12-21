/*
@license

dhtmlxChart v.9.1.3 GPL

This software is covered by GPL license.
To use it in non-GPL project, you need obtain Commercial or Enterprise license
Please contact sales@dhtmlx.com. Usage without proper license is prohibited.
(c) XB Software.

*/
if (window.dhx){ window.dhx_legacy = dhx; delete window.dhx; }(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["dhx"] = factory();
	else
		root["dhx"] = factory();
})(window, function() {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/codebase/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 34);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.getTextLines = exports.getCloneObject = exports.rgbToHex = exports.getStringWidth = exports.getMinArrayNumber = exports.getMaxArrayNumber = exports.isEmptyObj = exports.isType = exports.compare = exports.debounce = exports.downloadFile = exports.isNumeric = exports.range = exports.isId = exports.isDefined = exports.wrapBox = exports.unwrapBox = exports.detectWidgetClick = exports.singleOuterClick = exports.isExistValue = exports.findIndex = exports.naturalSort = exports.copy = exports.extend = exports.extendComponent = exports.uid = void 0;
var html_1 = __webpack_require__(4);
var counter = new Date().valueOf();
function uid() {
    return "u" + counter++;
}
exports.uid = uid;
function bindFunctionality(target, source, key) {
    var srcObj = source[key];
    if (typeof srcObj === "function") {
        target[key] = function () {
            var args = [];
            for (var _i = 0; _i < arguments.length; _i++) {
                args[_i] = arguments[_i];
            }
            return source[key].apply(source, args);
        };
    }
    else {
        Object.defineProperty(target, key, {
            get: function () { return source[key]; },
            set: function (value) { return (source[key] = value); },
            enumerable: true,
            configurable: true,
        });
    }
}
function extendComponent(target, source) {
    if (!source)
        return target;
    for (var _i = 0, _a = Object.keys(source); _i < _a.length; _i++) {
        var key = _a[_i];
        bindFunctionality(target, source, key);
    }
    var proto = Object.getPrototypeOf(source);
    while (proto && proto !== Object.prototype) {
        for (var _b = 0, _c = Object.getOwnPropertyNames(proto); _b < _c.length; _b++) {
            var key = _c[_b];
            if (!target.hasOwnProperty(key)) {
                bindFunctionality(target, source, key);
            }
        }
        proto = Object.getPrototypeOf(proto);
    }
    return target;
}
exports.extendComponent = extendComponent;
function extend(target, source, deep) {
    if (deep === void 0) { deep = true; }
    if (source) {
        for (var key in source) {
            var sobj = source[key];
            var tobj = target[key];
            if (sobj === undefined) {
                delete target[key];
            }
            else if (deep &&
                typeof tobj === "object" &&
                !(tobj instanceof Date) &&
                !(tobj instanceof Array)) {
                extend(tobj, sobj);
            }
            else {
                target[key] = sobj;
            }
        }
    }
    return target;
}
exports.extend = extend;
function copy(source, withoutInner) {
    var result = {};
    for (var key in source) {
        if (!withoutInner || !key.startsWith("$")) {
            result[key] = source[key];
        }
    }
    return result;
}
exports.copy = copy;
function naturalSort(arr) {
    return arr.sort(function (a, b) {
        var nn = typeof a === "string" ? a.localeCompare(b) : a - b;
        return nn;
    });
}
exports.naturalSort = naturalSort;
function findIndex(arr, predicate) {
    var len = arr.length;
    for (var i = 0; i < len; i++) {
        if (predicate(arr[i])) {
            return i;
        }
    }
    return -1;
}
exports.findIndex = findIndex;
function isExistValue(target, value) {
    var str = value.toString();
    var text = target.toString();
    if (str.length > text.length)
        return false;
    return text.toLowerCase().includes(str.toLowerCase());
}
exports.isExistValue = isExistValue;
function singleOuterClick(fn) {
    var click = function (e) {
        if (fn(e)) {
            document.removeEventListener("click", click);
        }
    };
    document.addEventListener("click", click);
}
exports.singleOuterClick = singleOuterClick;
function detectWidgetClick(widgetId, cb) {
    var click = function (e) { return cb((0, html_1.locate)(e, "data-dhx-widget-id") === widgetId); };
    document.addEventListener("click", click);
    return function () { return document.removeEventListener("click", click); };
}
exports.detectWidgetClick = detectWidgetClick;
function unwrapBox(box) {
    if (Array.isArray(box)) {
        return box[0];
    }
    return box;
}
exports.unwrapBox = unwrapBox;
function wrapBox(unboxed) {
    if (Array.isArray(unboxed)) {
        return unboxed;
    }
    return [unboxed];
}
exports.wrapBox = wrapBox;
function isDefined(some) {
    return some !== null && some !== undefined;
}
exports.isDefined = isDefined;
function isId(some) {
    return typeof some === "number" || (typeof some === "string" && some !== "");
}
exports.isId = isId;
function range(from, to) {
    if (from > to) {
        return [];
    }
    var result = [];
    while (from <= to) {
        result.push(from++);
    }
    return result;
}
exports.range = range;
function isNumeric(val) {
    return !isNaN(val - parseFloat(val));
}
exports.isNumeric = isNumeric;
function downloadFile(data, filename, mimeType) {
    if (mimeType === void 0) { mimeType = "text/plain"; }
    var file = new Blob([data], { type: mimeType });
    if (window.navigator.msSaveOrOpenBlob) {
        // IE10+
        window.navigator.msSaveOrOpenBlob(file, filename);
    }
    else {
        var a_1 = document.createElement("a");
        var url_1 = URL.createObjectURL(file);
        a_1.href = url_1;
        a_1.download = filename;
        document.body.appendChild(a_1);
        a_1.click();
        setTimeout(function () {
            document.body.removeChild(a_1);
            window.URL.revokeObjectURL(url_1);
        }, 0);
    }
}
exports.downloadFile = downloadFile;
function debounce(func, wait, immediate) {
    var timeout;
    return function executedFunction() {
        var _this = this;
        var args = [];
        for (var _i = 0; _i < arguments.length; _i++) {
            args[_i] = arguments[_i];
        }
        var later = function () {
            timeout = null;
            if (!immediate) {
                func.apply(_this, args);
            }
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) {
            func.apply(this, args);
        }
    };
}
exports.debounce = debounce;
function compare(obj1, obj2) {
    for (var p in obj1) {
        if (obj1.hasOwnProperty(p) !== obj2.hasOwnProperty(p)) {
            return false;
        }
        switch (typeof obj1[p]) {
            case "object":
                if (!compare(obj1[p], obj2[p])) {
                    return false;
                }
                break;
            case "function":
                if (typeof obj2[p] === "undefined" ||
                    (p !== "compare" && obj1[p].toString() !== obj2[p].toString())) {
                    return false;
                }
                break;
            default:
                if (obj1[p] !== obj2[p]) {
                    return false;
                }
        }
    }
    for (var p in obj2) {
        if (!obj1.hasOwnProperty(p)) {
            return false;
        }
    }
    return true;
}
exports.compare = compare;
var isType = function (value) {
    var regex = /^\[object (\S+?)\]$/;
    var matches = Object.prototype.toString.call(value).match(regex) || [];
    return (matches[1] || "undefined").toLowerCase();
};
exports.isType = isType;
var isEmptyObj = function (obj) {
    for (var key in obj) {
        return false;
    }
    return true;
};
exports.isEmptyObj = isEmptyObj;
var getMaxArrayNumber = function (array) {
    if (!array.length)
        return;
    var maxNumber = -Infinity;
    var index = 0;
    var length = array.length;
    for (index; index < length; index++) {
        if (array[index] > maxNumber)
            maxNumber = array[index];
    }
    return maxNumber;
};
exports.getMaxArrayNumber = getMaxArrayNumber;
var getMinArrayNumber = function (array) {
    if (!array.length)
        return;
    var minNumber = +Infinity;
    var index = 0;
    var length = array.length;
    for (index; index < length; index++) {
        if (array[index] < minNumber)
            minNumber = array[index];
    }
    return minNumber;
};
exports.getMinArrayNumber = getMinArrayNumber;
var getStringWidth = function (value, config) {
    config = __assign({ font: "normal 14px Roboto", lineHeight: 20 }, config);
    var canvas = document.createElement("canvas");
    var ctx = canvas.getContext("2d");
    if (config.font)
        ctx.font = config.font;
    var width = ctx.measureText(value).width;
    if (!(0, html_1.isIE)())
        canvas.remove();
    return width;
};
exports.getStringWidth = getStringWidth;
var rgbToHex = function (color) {
    if (color.substr(0, 1) === "#") {
        return color;
    }
    if (color.substr(0, 3) !== "rgb") {
        return;
    }
    var digits = /(.*?)rgb[a]?\((\d+), *(\d+), *(\d+),* *([\d+.]*)\)/.exec(color);
    var red = parseInt(digits[2], 10)
        .toString(16)
        .padStart(2, "0");
    var green = parseInt(digits[3], 10)
        .toString(16)
        .padStart(2, "0");
    var blue = parseInt(digits[4], 10)
        .toString(16)
        .padStart(2, "0");
    return "#".concat(red).concat(green).concat(blue);
};
exports.rgbToHex = rgbToHex;
function getCloneObject(obj) {
    if (!obj) {
        return obj;
    }
    var clone = Array.isArray(obj) ? [] : {};
    for (var key in obj) {
        var value = obj[key];
        if (value instanceof Date) {
            clone[key] = new Date(value);
            continue;
        }
        clone[key] = typeof value === "object" ? getCloneObject(value) : value;
    }
    return clone;
}
exports.getCloneObject = getCloneObject;
var getTextWidth = function (_a) {
    var text = _a.text, ctx = _a.ctx;
    var metrics = ctx.measureText(text);
    var width = metrics.width;
    return width;
};
var splitLongWord = function (_a) {
    var ctx = _a.ctx, word = _a.word, maxWidth = _a.maxWidth;
    var result = [];
    var part = "";
    for (var i = 0; i < word.length; i++) {
        part += word[i];
        if (getTextWidth({ text: part, ctx: ctx }) > maxWidth) {
            result.push(part.slice(0, -1));
            part = word[i];
        }
    }
    if (part)
        result.push(part);
    return result;
};
function getTextLines(_a) {
    var ctx = _a.ctx, text = _a.text, maxWidth = _a.maxWidth;
    var words = text.split(" ");
    var line = "";
    var testLine = "";
    var lineArray = [];
    for (var n = 0; n < words.length; n++) {
        var word = words[n];
        if (getTextWidth({ text: word, ctx: ctx }) > maxWidth) {
            var splitWords = splitLongWord({ word: word, ctx: ctx, maxWidth: maxWidth });
            for (var _i = 0, splitWords_1 = splitWords; _i < splitWords_1.length; _i++) {
                var splitWord = splitWords_1[_i];
                testLine += "".concat(splitWord, " ");
                var testWidth = getTextWidth({ text: testLine.trimEnd(), ctx: ctx });
                if (testWidth > maxWidth && n > 0) {
                    lineArray.push(line.trimEnd());
                    line = "".concat(splitWord, " ");
                    testLine = "".concat(splitWord, " ");
                }
                else {
                    line += "".concat(splitWord, " ");
                }
            }
        }
        else {
            testLine += "".concat(word, " ");
            var testWidth = getTextWidth({ text: testLine.trimEnd(), ctx: ctx });
            if (testWidth > maxWidth && n > 0) {
                lineArray.push(line.trimEnd());
                line = "".concat(word, " ");
                testLine = "".concat(word, " ");
            }
            else {
                line += "".concat(word, " ");
            }
        }
        if (n === words.length - 1) {
            lineArray.push(line.trimEnd());
        }
    }
    return lineArray;
}
exports.getTextLines = getTextLines;


/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(Promise) {
Object.defineProperty(exports, "__esModule", { value: true });
exports.getViewHeight = exports.setTheme = exports.awaitRedraw = exports.resizeHandler = exports.resizer = exports.disableHelp = exports.KEYED_LIST = exports.inject = exports.create = exports.view = exports.sv = exports.el = void 0;
var dom = __webpack_require__(46);
var html_1 = __webpack_require__(4);
exports.el = dom.defineElement;
exports.sv = dom.defineSvgElement;
exports.view = dom.defineView;
exports.create = dom.createView;
exports.inject = dom.injectView;
exports.KEYED_LIST = dom.KEYED_LIST;
function disableHelp() {
    dom.DEVMODE.mutations = false;
    dom.DEVMODE.warnings = false;
    dom.DEVMODE.verbose = false;
    dom.DEVMODE.UNKEYED_INPUT = false;
}
exports.disableHelp = disableHelp;
function resizer(handler) {
    var resize = window.ResizeObserver;
    var activeHandler = function (node) {
        var height = node.el.offsetHeight;
        var width = node.el.offsetWidth;
        handler(width, height);
    };
    if (resize) {
        return (0, exports.el)("div.dhx-resize-observer", {
            _hooks: {
                didInsert: function (node) {
                    new resize(function () { return activeHandler(node); }).observe(node.el);
                },
            },
        });
    }
    return (0, exports.el)("iframe.dhx-resize-observer", {
        _hooks: {
            didInsert: function (node) {
                node.el.contentWindow.onresize = function () { return activeHandler(node); };
                activeHandler(node);
            },
        },
    });
}
exports.resizer = resizer;
function resizeHandler(container, handler) {
    return (0, exports.create)({
        render: function () {
            return resizer(handler);
        },
    }).mount(container);
}
exports.resizeHandler = resizeHandler;
function awaitRedraw() {
    return new Promise(function (res) {
        requestAnimationFrame(function () {
            res();
        });
    });
}
exports.awaitRedraw = awaitRedraw;
function setTheme(theme, container) {
    if (theme === void 0) { theme = "light"; }
    var dhxAttr = "data-dhx-theme";
    if (!container) {
        var elements = document.querySelectorAll("[".concat(dhxAttr, "]"));
        elements.forEach(function (el) { return el.removeAttribute(dhxAttr); });
    }
    container = container || document.documentElement;
    (0, html_1.toNode)(container).setAttribute(dhxAttr, theme);
}
exports.setTheme = setTheme;
function getViewHeight(view, width) {
    var vm = (0, exports.create)({
        render: function () {
            return (0, exports.el)("div", {
                style: {
                    position: "absolute",
                    visibility: "hidden",
                    width: width,
                },
            }, [view]);
        },
    });
    vm.mount(document.body);
    var height = vm.node.el.offsetHeight;
    vm.unmount();
    return height;
}
exports.getViewHeight = getViewHeight;

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(6)))

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.checkPositions = exports.superposition = exports.getSizesSVGText = exports.getScales = exports.getClassesForRotateScale = exports.calcPointRef = exports.verticalBottomText = exports.verticalTopText = exports.verticalCenteredText = exports.roundToTwoNumAfterPoint = exports.euclideanDistance = exports.getRadialGradient = exports.linearGradient = exports.getFontStyle = exports.getColorShade = exports.getTextWidth = exports.log10 = exports.locator = exports.getDefaultColor = void 0;
var dom_1 = __webpack_require__(1);
var defaultColors = [
    "#2A9D8F",
    "#78586F",
    "#E76F51",
    "#E5A910",
    "#11A3D0",
    "#985F99",
    "#217B70",
    "#BD9391",
    "#9C89B8",
    "#734B5E",
    "#D66BA0",
    "#5C5D8D",
];
var defaultColorsTreeMap = ["#237396", "#2780A8", "#3892A3", "#4DA3A0", "#67BF99"];
function getDefaultColor(index, isTreeMapRange) {
    if (index === void 0) { index = 0; }
    return isTreeMapRange ? defaultColorsTreeMap[index] : defaultColors[index];
}
exports.getDefaultColor = getDefaultColor;
function locator(value) {
    if (!value) {
        return function () { return ""; };
    }
    if (typeof value === "string") {
        return function (obj) { return obj[value]; };
    }
    else {
        return value;
    }
}
exports.locator = locator;
function log10(x) {
    return Math.log(x) / Math.LN10;
}
exports.log10 = log10;
function anyArgsMemo(fn) {
    var cached = {};
    return function () {
        var args = [];
        for (var _i = 0; _i < arguments.length; _i++) {
            args[_i] = arguments[_i];
        }
        var mem = cached;
        for (var i = 0; i < args.length - 1; i++) {
            mem[args[i]] = mem[args[i]] || {};
            mem = mem[args[i]];
        }
        var last = args.length - 1;
        if (mem[last]) {
            return mem[last];
        }
        return (mem[last] = fn.apply(void 0, args));
    };
}
exports.getTextWidth = anyArgsMemo(function (text, font) {
    if (font === void 0) { font = ""; }
    var canvas = document.createElement("canvas");
    var ctx = canvas.getContext("2d");
    if (font) {
        ctx.font = font;
    }
    return ctx.measureText(text).width;
});
function memo(fn) {
    var cached = {};
    return function (arg) {
        if (cached[arg]) {
            return cached[arg];
        }
        return (cached[arg] = fn(arg));
    };
}
function getRgbaFromColor(color) {
    var canvas = document.createElement("canvas");
    var ctx = canvas.getContext("2d");
    ctx.fillStyle = color;
    ctx.fillRect(0, 0, 2, 2);
    var rgba = ctx.getImageData(1, 1, 1, 1).data;
    return [rgba[0], rgba[1], rgba[2]];
}
var memoizedColorFromRgba = memo(getRgbaFromColor);
function getColorShade(color, light) {
    var _a = memoizedColorFromRgba(color).map(function (value) {
        return Math.floor(value * light + 255 * (1 - light));
    }), r = _a[0], g = _a[1], b = _a[2];
    return "rgb(".concat(r, ",").concat(g, ",").concat(b, ")");
}
exports.getColorShade = getColorShade;
exports.getFontStyle = memo(function (className) {
    var chart = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    chart.setAttribute("class", "dhx_chart");
    var text = document.createElementNS("http://www.w3.org/2000/svg", "text");
    text.setAttribute("class", className);
    chart.setAttribute("visibility", "hidden");
    text.textContent = "test";
    chart.appendChild(text);
    document.body.appendChild(chart);
    var style = getComputedStyle(text);
    var font = "".concat(style.fontSize, " ").concat(style.fontFamily);
    document.body.removeChild(chart);
    return font;
});
function linearGradient(grad, id) {
    var stops = grad.stops;
    var colors = stops.map(function (item) {
        return (0, dom_1.sv)("stop", {
            offset: "".concat(item.offset * 100, "%"),
            "stop-color": item.color,
            "stop-opacity": item.opacity || 1,
        });
    });
    var gradient = (0, dom_1.sv)("linearGradient", {
        id: id,
        gradientTransform: "rotate(90)",
    }, colors);
    return gradient;
}
exports.linearGradient = linearGradient;
function getRadialGradient(opts, stops, id) {
    var colors = stops.map(function (item) {
        return (0, dom_1.sv)("stop", {
            offset: item.offset,
            "stop-color": item.color,
            "stop-opacity": item.opacity || 1,
        });
    });
    var gradient = (0, dom_1.sv)("radialGradient", __assign({ id: id, cx: 0, cy: 0, gradientUnits: "userSpaceOnUse" }, opts), colors);
    return gradient;
}
exports.getRadialGradient = getRadialGradient;
function euclideanDistance(x1, y1, x2, y2) {
    return Math.sqrt((x1 - x2) * (x1 - x2) + (y1 - y2) * (y1 - y2));
}
exports.euclideanDistance = euclideanDistance;
function roundToTwoNumAfterPoint(p) {
    return Math.round((p * 100 + Number.EPSILON) * 100) / 100;
}
exports.roundToTwoNumAfterPoint = roundToTwoNumAfterPoint;
function verticalCenteredText(text) {
    return (0, dom_1.sv)("tspan", {
        dy: "0.5ex",
        style: {
            pointerEvents: "none",
        },
    }, text);
}
exports.verticalCenteredText = verticalCenteredText;
function verticalTopText(text) {
    return (0, dom_1.sv)("tspan", {
        dy: "-0.5ex",
    }, text);
}
exports.verticalTopText = verticalTopText;
function verticalBottomText(text) {
    return (0, dom_1.sv)("tspan", {
        dy: "1.5ex",
    }, text);
}
exports.verticalBottomText = verticalBottomText;
function calcPointRef(pointId, serieId) {
    return pointId + "_" + serieId;
}
exports.calcPointRef = calcPointRef;
function getClassesForRotateScale(position, angle) {
    var className = "";
    var classList = [];
    if (position === "left" || position === "top") {
        classList.push("start-text", "end-text");
    }
    else if (position === "right" || position === "bottom") {
        classList.push("end-text", "start-text");
    }
    switch (position) {
        case "left":
        case "right":
            if (angle === 0) {
                className = classList[1];
            }
            else if (angle > 0) {
                if (angle === 180) {
                    className = classList[0];
                }
                else if (angle > 180) {
                    if (angle < 270) {
                        className = classList[0];
                    }
                    else if (angle > 270) {
                        className = classList[1];
                    }
                }
                else if (angle < 180) {
                    if (angle > 90) {
                        className = classList[0];
                    }
                    else if (angle < 90) {
                        className = classList[1];
                    }
                }
            }
            else if (angle < 0) {
                if (angle === -180) {
                    className = classList[0];
                }
                else if (angle < -180) {
                    if (angle > -270) {
                        className = classList[0];
                    }
                    else if (angle < -270) {
                        className = classList[1];
                    }
                }
                else if (angle > -180) {
                    if (angle < -90) {
                        className = classList[0];
                    }
                    else if (angle > -90) {
                        className = classList[1];
                    }
                }
            }
            break;
        case "top":
        case "bottom":
            if (angle > 0) {
                if (angle > 180) {
                    className = classList[0];
                }
                else if (angle < 180) {
                    className = classList[1];
                }
            }
            else if (angle < 0) {
                if (angle > -180) {
                    className = classList[0];
                }
                else if (angle < -180) {
                    className = classList[1];
                }
            }
            break;
    }
    return className;
}
exports.getClassesForRotateScale = getClassesForRotateScale;
function getScales(config) {
    var scales = [];
    for (var scaleName in config) {
        var scale = config[scaleName];
        if (scale.min || scale.max || scale.maxTicks || scale.text || scale.value) {
            scales.push(scaleName);
        }
    }
    return scales;
}
exports.getScales = getScales;
function getSizesSVGText(text, config) {
    var sizes = [];
    config = __assign({ font: "normal 14px Roboto", lineHeight: 18 }, config);
    sizes.push((0, exports.getTextWidth)(text, config.font));
    sizes.push(config.lineHeight);
    return sizes;
}
exports.getSizesSVGText = getSizesSVGText;
function superposition(objA, objB) {
    if (objA.x < objB.x + objB.width &&
        objA.x + objA.width > objB.x &&
        objA.y < objB.y + objB.height &&
        objA.y + objA.height > objB.y) {
        return true;
    }
    else
        return false;
}
exports.superposition = superposition;
function checkPositions(current, previos, radiusX, radiusY, obj) {
    if (superposition(current, previos)) {
        var dY = obj.right
            ? previos.y - current.y + current.height
            : previos.y - previos.height - current.y;
        var newY = obj.text1.y + dY;
        if (Math.abs(newY) + obj.dy > radiusY) {
            newY = newY > 0 ? radiusY + obj.dy : -radiusY + obj.dy;
            obj.changeSector = !obj.changeSector;
        }
        var newX = Math.sqrt(Math.pow(radiusX, 2) - Math.pow(((newY - obj.dy) * radiusX) / radiusY, 2));
        newX = obj.right ? newX : -newX;
        if (obj.changeSector) {
            newX *= -1;
            obj.line *= -1;
            current.class = obj.right ? "pie-value end-text" : "pie-value start-text";
        }
        obj.text1.x = newX;
        obj.text1.y = newY;
        obj.text2.x = newX;
        obj.text2.y = newY + 16;
    }
}
exports.checkPositions = checkPositions;


/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.splitCsv = exports.throwMoveWarning = exports.isOnlyPermanentFilters = exports.hasJsonOrArrayStructure = exports.isTreeCollection = exports.copyWithoutInner = exports.toDataDriver = exports.toProxy = exports.dhxError = exports.dhxWarning = exports.isDebug = exports.findByConf = exports.naturalCompare = exports.isEqualObj = void 0;
var core_1 = __webpack_require__(0);
var dataproxy_1 = __webpack_require__(8);
var drivers_1 = __webpack_require__(22);
function isEqualObj(a, b) {
    for (var key in a) {
        if (a[key] !== b[key] || Array.isArray(a[key])) {
            return false;
        }
    }
    return true;
}
exports.isEqualObj = isEqualObj;
function naturalCompare(a, b) {
    if (isNaN(a) || isNaN(b)) {
        var ax_1 = [];
        var bx_1 = [];
        a.replace(/(\d+)|(\D+)/g, function (_, $1, $2) {
            ax_1.push([$1 || Infinity, $2 || ""]);
        });
        b.replace(/(\d+)|(\D+)/g, function (_, $1, $2) {
            bx_1.push([$1 || Infinity, $2 || ""]);
        });
        while (ax_1.length && bx_1.length) {
            var an = ax_1.shift();
            var bn = bx_1.shift();
            var nn = an[0] - bn[0] || an[1].localeCompare(bn[1]);
            if (nn) {
                return nn;
            }
        }
        return ax_1.length - bx_1.length;
    }
    return a - b;
}
exports.naturalCompare = naturalCompare;
function findByConf(item, conf, index, array) {
    if (typeof conf === "function") {
        if (conf.call(this, item, index, array)) {
            return item;
        }
    }
    else if (conf.by && conf.match) {
        if (item[conf.by] === conf.match) {
            return item;
        }
    }
}
exports.findByConf = findByConf;
function isDebug() {
    var dhx = window.dhx;
    if (typeof dhx !== "undefined") {
        return typeof dhx.debug !== "undefined" && dhx.debug;
    }
    // return typeof DHX_DEBUG_MODE !== "undefined" && DHX_DEBUG_MODE;
}
exports.isDebug = isDebug;
function dhxWarning(msg) {
    // tslint:disable-next-line:no-console
    console.warn(msg);
}
exports.dhxWarning = dhxWarning;
function dhxError(msg) {
    throw new Error(msg);
}
exports.dhxError = dhxError;
function toProxy(proxy) {
    var type = typeof proxy;
    if (type === "string") {
        return new dataproxy_1.DataProxy(proxy);
    }
    else if (type === "object") {
        return proxy;
    }
}
exports.toProxy = toProxy;
function toDataDriver(driver) {
    if (typeof driver === "string") {
        var dhx = window.dhx;
        var drivers = (dhx && dhx.dataDrivers) || drivers_1.dataDrivers;
        if (drivers[driver]) {
            return new drivers[driver]();
        }
        else {
            // tslint:disable-next-line:no-console
            console.warn("Incorrect data driver type:", driver);
            // tslint:disable-next-line:no-console
            console.warn("Available types:", JSON.stringify(Object.keys(drivers)));
        }
    }
    else if (typeof driver === "object") {
        return driver;
    }
}
exports.toDataDriver = toDataDriver;
function copyWithoutInner(obj, forbidden) {
    var result = {};
    for (var key in obj) {
        if (!key.startsWith("$") && (!forbidden || !forbidden[key])) {
            result[key] = obj[key];
        }
    }
    return result;
}
exports.copyWithoutInner = copyWithoutInner;
function isTreeCollection(obj) {
    // eslint-disable-next-line @typescript-eslint/unbound-method
    return Boolean(obj.getRoot);
}
exports.isTreeCollection = isTreeCollection;
function hasJsonOrArrayStructure(str) {
    if (typeof str === "object") {
        return true;
    }
    if (typeof str !== "string") {
        return false;
    }
    try {
        var result = JSON.parse(str);
        return Object.prototype.toString.call(result) === "[object Object]" || Array.isArray(result);
    }
    catch (err) {
        return false;
    }
}
exports.hasJsonOrArrayStructure = hasJsonOrArrayStructure;
function isOnlyPermanentFilters(filters) {
    if (!filters || (0, core_1.isEmptyObj)(filters))
        return false;
    return Object.keys(filters).every(function (key) {
        var _a;
        return (_a = filters[key].config) === null || _a === void 0 ? void 0 : _a.permanent;
    });
}
exports.isOnlyPermanentFilters = isOnlyPermanentFilters;
function throwMoveWarning(id, exists) {
    if (exists === void 0) { exists = true; }
    dhxWarning("item with ID ".concat(id, " not ").concat(exists ? "moved" : "found"));
    return null;
}
exports.throwMoveWarning = throwMoveWarning;
function splitCsv(csv, delimiter) {
    return csv.trim().split(new RegExp("".concat(delimiter, "(?=(?:[^\"]*\"[^\"]*\")*[^\"]*$)")));
}
exports.splitCsv = splitCsv;


/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.getNodeOffset = exports.getElementFromPoint = exports.getPageLinksCss = exports.getPageInlineCss = exports.getLabelStyle = exports.getPageCss = exports.fitPosition = exports.calculatePosition = exports.getRealPosition = exports.isFirefox = exports.isSafari = exports.isIE = exports.getScrollbarHeight = exports.getScrollbarWidth = exports.getBox = exports.locateNodeByClassName = exports.locate = exports.locateNode = exports.eventHandler = exports.toNode = void 0;
function toNode(node) {
    var _a;
    return typeof node === "string"
        ? document.getElementById(node) || document.querySelector("[data-cell-id=".concat(node, "]")) || document.querySelector(node) || ((_a = document.querySelector("[data-dhx-root-id=".concat(node, "]"))) === null || _a === void 0 ? void 0 : _a.parentElement) || document.body
        : node || document.body;
}
exports.toNode = toNode;
function eventHandler(prepare, hash, afterCall) {
    var keys = Object.keys(hash);
    return function (ev) {
        var data = prepare(ev);
        if (data !== undefined) {
            var node = ev.target;
            outer_block: while (node) {
                var cssstring = node.getAttribute ? node.getAttribute("class") || "" : "";
                if (cssstring.length) {
                    var css = cssstring.split(" ");
                    for (var j = 0; j < keys.length; j++) {
                        if (css.includes(keys[j])) {
                            if (hash[keys[j]](ev, data) === false || ev.cancelBubble)
                                return false;
                            else
                                break outer_block;
                        }
                    }
                }
                node = node.parentNode;
            }
        }
        if (typeof afterCall === "function")
            afterCall(ev);
        return true;
    };
}
exports.eventHandler = eventHandler;
function locateNode(target, attr, dir) {
    if (attr === void 0) { attr = "data-dhx-id"; }
    if (dir === void 0) { dir = "target"; }
    if (target instanceof Event) {
        target = target[dir];
    }
    while (target) {
        if (target.getAttribute && target.getAttribute(attr)) {
            return target;
        }
        target = target.parentNode;
    }
}
exports.locateNode = locateNode;
function locate(target, attr) {
    if (attr === void 0) { attr = "data-dhx-id"; }
    var node = locateNode(target, attr);
    return node ? node.getAttribute(attr) : "";
}
exports.locate = locate;
function locateNodeByClassName(target, className) {
    if (target instanceof Event) {
        target = target.target;
    }
    while (target) {
        if (className) {
            if (target.classList && target.classList.contains(className)) {
                return target;
            }
        }
        else if (target.getAttribute && target.getAttribute("data-dhx-id")) {
            return target;
        }
        target = target.parentNode;
    }
}
exports.locateNodeByClassName = locateNodeByClassName;
function getBox(elem) {
    var box = elem.getBoundingClientRect();
    var body = document.body;
    var scrollTop = window.pageYOffset || body.scrollTop;
    var scrollLeft = window.pageXOffset || body.scrollLeft;
    var top = box.top + scrollTop;
    var left = box.left + scrollLeft;
    var right = body.offsetWidth - box.right;
    var bottom = body.offsetHeight - box.bottom;
    var width = box.right - box.left;
    var height = box.bottom - box.top;
    return { top: top, left: left, right: right, bottom: bottom, width: width, height: height };
}
exports.getBox = getBox;
var scrollWidth = -1;
function getScrollbarWidth() {
    if (scrollWidth > -1) {
        return scrollWidth;
    }
    var scrollDiv = document.createElement("div");
    document.body.appendChild(scrollDiv);
    scrollDiv.style.cssText = "position: absolute;left: -99999px;overflow:scroll;width: 100px;height: 100px;";
    scrollWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth;
    document.body.removeChild(scrollDiv);
    return scrollWidth;
}
exports.getScrollbarWidth = getScrollbarWidth;
var scrollHeight = -1;
function getScrollbarHeight() {
    if (scrollHeight > -1) {
        return scrollHeight;
    }
    var scrollDiv = document.createElement("div");
    document.body.appendChild(scrollDiv);
    scrollDiv.style.cssText = "position: absolute;left: -99999px;overflow:scroll;width: 100px;height: 100px;";
    scrollHeight = scrollDiv.offsetHeight - scrollDiv.clientHeight;
    document.body.removeChild(scrollDiv);
    return scrollHeight;
}
exports.getScrollbarHeight = getScrollbarHeight;
function isIE() {
    var ua = window.navigator.userAgent;
    return ua.includes("MSIE ") || ua.includes("Trident/");
}
exports.isIE = isIE;
function isSafari() {
    var check = function (str) { return str.test(window.navigator.userAgent); };
    var chrome = check(/Chrome/);
    var firefox = check(/Firefox/);
    return !chrome && !firefox && check(/Safari/);
}
exports.isSafari = isSafari;
function isFirefox() {
    var check = function (str) { return str.test(window.navigator.userAgent); };
    var chrome = check(/Chrome/);
    var safari = check(/Safari/);
    return !chrome && !safari && check(/Firefox/);
}
exports.isFirefox = isFirefox;
function getRealPosition(node) {
    var rects = node.getBoundingClientRect();
    return {
        left: rects.left + window.pageXOffset,
        right: rects.right + window.pageXOffset,
        top: rects.top + window.pageYOffset,
        bottom: rects.bottom + window.pageYOffset,
    };
}
exports.getRealPosition = getRealPosition;
function getWindowBorders() {
    return {
        rightBorder: window.pageXOffset + window.innerWidth,
        bottomBorder: window.pageYOffset + window.innerHeight,
    };
}
function horizontalCentering(pos, width, rightBorder) {
    var nodeWidth = pos.right - pos.left;
    var diff = (width - nodeWidth) / 2;
    var left = pos.left - diff;
    var right = pos.right + diff;
    if (left >= 0 && right <= rightBorder) {
        return left;
    }
    if (left < 0) {
        return 0;
    }
    return rightBorder - width;
}
function verticalCentering(pos, height, bottomBorder) {
    var nodeHeight = pos.bottom - pos.top;
    var diff = (height - nodeHeight) / 2;
    var top = pos.top - diff;
    var bottom = pos.bottom + diff;
    if (top >= 0 && bottom <= bottomBorder) {
        return top;
    }
    if (top < 0) {
        return 0;
    }
    return bottomBorder - height;
}
function placeBottomOrTop(pos, config) {
    var _a = getWindowBorders(), rightBorder = _a.rightBorder, bottomBorder = _a.bottomBorder;
    var left;
    var top;
    var bottomDiff = bottomBorder - pos.bottom - config.height;
    var topDiff = pos.top - config.height;
    if (config.mode === "bottom") {
        if (bottomDiff >= 0) {
            top = pos.bottom;
        }
        else if (topDiff >= 0) {
            top = topDiff;
        }
    }
    else {
        if (topDiff >= 0) {
            top = topDiff;
        }
        else if (bottomDiff >= 0) {
            top = pos.bottom;
        }
    }
    if (bottomDiff < 0 && topDiff < 0) {
        if (config.auto) {
            // eslint-disable-next-line @typescript-eslint/no-use-before-define
            return placeRightOrLeft(pos, __assign(__assign({}, config), { mode: "right", auto: false }));
        }
        top = bottomDiff > topDiff ? pos.bottom : topDiff;
    }
    if (config.centering) {
        left = horizontalCentering(pos, config.width, rightBorder);
    }
    else {
        var leftDiff = rightBorder - pos.left - config.width;
        var rightDiff = pos.right - config.width;
        if (leftDiff >= 0) {
            left = pos.left;
        }
        else if (rightDiff >= 0) {
            left = rightDiff;
        }
        else {
            left = rightDiff > leftDiff ? pos.left : rightDiff;
        }
    }
    return { left: left, top: top };
}
function placeRightOrLeft(pos, config) {
    var _a = getWindowBorders(), rightBorder = _a.rightBorder, bottomBorder = _a.bottomBorder;
    var left;
    var top;
    var rightDiff = rightBorder - pos.right - config.width;
    var leftDiff = pos.left - config.width;
    if (config.mode === "right") {
        if (rightDiff >= 0) {
            left = pos.right;
        }
        else if (leftDiff >= 0) {
            left = leftDiff;
        }
    }
    else {
        if (leftDiff >= 0) {
            left = leftDiff;
        }
        else if (rightDiff >= 0) {
            left = pos.right;
        }
    }
    if (leftDiff < 0 && rightDiff < 0) {
        if (config.auto) {
            return placeBottomOrTop(pos, __assign(__assign({}, config), { mode: "bottom", auto: false }));
        }
        left = leftDiff > rightDiff ? leftDiff : pos.right;
    }
    if (config.centering) {
        top = verticalCentering(pos, config.height, bottomBorder);
    }
    else {
        var bottomDiff = pos.bottom - config.height;
        var topDiff = bottomBorder - pos.top - config.height;
        if (topDiff >= 0) {
            top = pos.top;
        }
        else if (bottomDiff > 0) {
            top = bottomDiff;
        }
        else {
            top = bottomDiff > topDiff ? bottomDiff : pos.top;
        }
    }
    return { left: left, top: top };
}
function calculatePosition(pos, config) {
    var _a = config.mode === "bottom" || config.mode === "top"
        ? placeBottomOrTop(pos, config)
        : placeRightOrLeft(pos, config), left = _a.left, top = _a.top;
    return {
        left: Math.round(left) + "px",
        top: Math.round(top) + "px",
        minWidth: Math.round(config.width) + "px",
        position: "absolute",
    };
}
exports.calculatePosition = calculatePosition;
function fitPosition(node, config) {
    return calculatePosition(getRealPosition(node), config);
}
exports.fitPosition = fitPosition;
function getPageCss() {
    var css = [];
    for (var sheeti = 0; sheeti < document.styleSheets.length; sheeti++) {
        var sheet = document.styleSheets[sheeti];
        var rules = "cssRules" in sheet ? sheet.cssRules : sheet.rules;
        for (var rulei = 0; rulei < rules.length; rulei++) {
            var rule = rules[rulei];
            if ("cssText" in rule) {
                css.push(rule.cssText);
            }
            else {
                css.push("".concat(rule.selectorText, " {\n").concat(rule.style.cssText, "\n}\n"));
            }
        }
    }
    return css.join("\n");
}
exports.getPageCss = getPageCss;
function getLabelStyle(config) {
    var helpMessage = config.helpMessage, type = config.type, labelWidth = config.labelWidth, label = config.label;
    var isZero = labelWidth && labelWidth.toString().startsWith("0");
    var required = type !== "text" && config.required;
    if (!helpMessage && !required && (!label || (label && isZero)) && (!labelWidth || isZero)) {
        return false;
    }
    return {
        style: (label || labelWidth) && !isZero && { width: labelWidth, "max-width": "100%" },
        label: label && isZero ? null : label,
    };
}
exports.getLabelStyle = getLabelStyle;
var checkCrossLink = function (sheet) { return sheet.href && !sheet.href.startsWith(window.location.origin); };
function getPageInlineCss() {
    var css = [];
    for (var i = 0; i < document.styleSheets.length; i++) {
        var sheet = document.styleSheets[i];
        if (!checkCrossLink(sheet)) {
            var rules = "cssRules" in sheet && sheet.cssRules.length
                ? sheet.cssRules
                : sheet.rules;
            for (var j = 0; j < rules.length; j++) {
                var rule = rules[j];
                if ("cssText" in rule) {
                    css.push(rule.cssText);
                }
                else {
                    css.push("".concat(rule.selectorText, " {\n").concat(rule.style.cssText, "\n}\n"));
                }
            }
        }
    }
    return css.join("\n");
}
exports.getPageInlineCss = getPageInlineCss;
function getPageLinksCss(exportStyles) {
    var css = [];
    if (exportStyles) {
        exportStyles.forEach(function (link) { return css.push("<link href=\"".concat(link, "\" rel=\"stylesheet\"/>")); });
    }
    else {
        for (var i = 0; i < document.styleSheets.length; i++) {
            var sheet = document.styleSheets[i];
            if (checkCrossLink(sheet)) {
                css.push("<link href=\"".concat(sheet.href, "\" rel=\"stylesheet\"/>"));
            }
        }
    }
    return css.join("\n");
}
exports.getPageLinksCss = getPageLinksCss;
function getElementFromPoint(e) {
    var _a, _b;
    var clientX = e.targetTouches
        ? ((_a = e.targetTouches[0]) === null || _a === void 0 ? void 0 : _a.clientX) || 0
        : e.clientX;
    var clientY = e.targetTouches
        ? ((_b = e.targetTouches[0]) === null || _b === void 0 ? void 0 : _b.clientY) || 0
        : e.clientY;
    var el = document.elementFromPoint(clientX, clientY);
    return (el === null || el === void 0 ? void 0 : el.shadowRoot) ? el.shadowRoot.elementFromPoint(clientX, clientY) : el;
}
exports.getElementFromPoint = getElementFromPoint;
function getNodeOffset(node1, node2) {
    var rect1 = node1.getBoundingClientRect();
    var rect2 = node2.getBoundingClientRect();
    return { left: rect1.left - rect2.left, top: rect1.top - rect2.top };
}
exports.getNodeOffset = getNodeOffset;


/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.DataDriver = exports.DragEvents = exports.DataEvents = exports.TreeFilterType = void 0;
var TreeFilterType;
(function (TreeFilterType) {
    TreeFilterType["all"] = "all";
    TreeFilterType["level"] = "level";
    TreeFilterType["leafs"] = "leafs";
})(TreeFilterType || (exports.TreeFilterType = TreeFilterType = {}));
var DataEvents;
(function (DataEvents) {
    DataEvents["afterAdd"] = "afteradd";
    DataEvents["beforeAdd"] = "beforeadd";
    DataEvents["removeAll"] = "removeall";
    DataEvents["beforeRemove"] = "beforeremove";
    DataEvents["afterRemove"] = "afterremove";
    DataEvents["change"] = "change";
    DataEvents["filter"] = "filter";
    DataEvents["dataRequest"] = "dataRequest";
    DataEvents["load"] = "load";
    DataEvents["loadError"] = "loaderror";
    DataEvents["beforeLazyLoad"] = "beforelazyload";
    DataEvents["afterLazyLoad"] = "afterlazyload";
    DataEvents["beforeItemLoad"] = "beforeItemLoad";
    DataEvents["afterItemLoad"] = "afterItemLoad";
    DataEvents["beforeGroup"] = "beforeGroup";
    DataEvents["afterGroup"] = "afterGroup";
    DataEvents["beforeUnGroup"] = "beforeUnGroup";
    DataEvents["afterUnGroup"] = "afterUnGroup";
})(DataEvents || (exports.DataEvents = DataEvents = {}));
var DragEvents;
(function (DragEvents) {
    DragEvents["beforeDrag"] = "beforeDrag";
    DragEvents["dragStart"] = "dragStart";
    DragEvents["dragOut"] = "dragOut";
    DragEvents["dragIn"] = "dragIn";
    DragEvents["canDrop"] = "canDrop";
    DragEvents["cancelDrop"] = "cancelDrop";
    DragEvents["beforeDrop"] = "beforeDrop";
    DragEvents["afterDrop"] = "afterDrop";
    DragEvents["afterDrag"] = "afterDrag";
})(DragEvents || (exports.DragEvents = DragEvents = {}));
var DataDriver;
(function (DataDriver) {
    DataDriver["json"] = "json";
    DataDriver["csv"] = "csv";
    DataDriver["xml"] = "xml";
})(DataDriver || (exports.DataDriver = DataDriver = {}));


/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(global, setImmediate) {(function () {
  global = typeof window !== 'undefined' ? window : this

  var queueId = 1
  var queue = {}
  var isRunningTask = false

  if (!global.setImmediate)
    global.addEventListener('message', function (e) {
      if (e.source == global){
        if (isRunningTask)
          nextTick(queue[e.data])
        else {
          isRunningTask = true
          try {
            queue[e.data]()
          } catch (e) {}

          delete queue[e.data]
          isRunningTask = false
        }
      }
    })

  function nextTick(fn) {
    if (global.setImmediate) setImmediate(fn)
    // if inside of web worker
    else if (global.importScripts) setTimeout(fn)
    else {
      queueId++
      queue[queueId] = fn
      global.postMessage(queueId, '*')
    }
  }

  Deferred.resolve = function (value) {
    if (!(this._d == 1))
      throw TypeError()

    if (value instanceof Deferred)
      return value

    return new Deferred(function (resolve) {
        resolve(value)
    })
  }

  Deferred.reject = function (value) {
    if (!(this._d == 1))
      throw TypeError()

    return new Deferred(function (resolve, reject) {
        reject(value)
    })
  }

  Deferred.all = function (arr) {
    if (!(this._d == 1))
      throw TypeError()

    if (!(arr instanceof Array))
      return Deferred.reject(TypeError())

    var d = new Deferred()

    function done(e, v) {
      if (v)
        return d.resolve(v)

      if (e)
        return d.reject(e)

      var unresolved = arr.reduce(function (cnt, v) {
        if (v && v.then)
          return cnt + 1
        return cnt
      }, 0)

      if(unresolved == 0)
        d.resolve(arr)

      arr.map(function (v, i) {
        if (v && v.then)
          v.then(function (r) {
            arr[i] = r
            done()
            return r
          }, done)
      })
    }

    done()

    return d
  }

  Deferred.race = function (arr) {
    if (!(this._d == 1))
      throw TypeError()

    if (!(arr instanceof Array))
      return Deferred.reject(TypeError())

    if (arr.length == 0)
      return new Deferred()

    var d = new Deferred()

    function done(e, v) {
      if (v)
        return d.resolve(v)

      if (e)
        return d.reject(e)

      var unresolved = arr.reduce(function (cnt, v) {
        if (v && v.then)
          return cnt + 1
        return cnt
      }, 0)

      if(unresolved == 0)
        d.resolve(arr)

      arr.map(function (v, i) {
        if (v && v.then)
          v.then(function (r) {
            done(null, r)
          }, done)
      })
    }

    done()

    return d
  }

  Deferred._d = 1


  /**
   * @constructor
   */
  function Deferred(resolver) {
    'use strict'
    if (typeof resolver != 'function' && resolver != undefined)
      throw TypeError()

    if (typeof this != 'object' || (this && this.then))
      throw TypeError()

    // states
    // 0: pending
    // 1: resolving
    // 2: rejecting
    // 3: resolved
    // 4: rejected
    var self = this,
      state = 0,
      val = 0,
      next = [],
      fn, er;

    self['promise'] = self

    self['resolve'] = function (v) {
      fn = self.fn
      er = self.er
      if (!state) {
        val = v
        state = 1

        nextTick(fire)
      }
      return self
    }

    self['reject'] = function (v) {
      fn = self.fn
      er = self.er
      if (!state) {
        val = v
        state = 2

        nextTick(fire)

      }
      return self
    }

    self['_d'] = 1

    self['then'] = function (_fn, _er) {
      if (!(this._d == 1))
        throw TypeError()

      var d = new Deferred()

      d.fn = _fn
      d.er = _er
      if (state == 3) {
        d.resolve(val)
      }
      else if (state == 4) {
        d.reject(val)
      }
      else {
        next.push(d)
      }

      return d
    }

    self['catch'] = function (_er) {
      return self['then'](null, _er)
    }

    var finish = function (type) {
      state = type || 4
      next.map(function (p) {
        state == 3 && p.resolve(val) || p.reject(val)
      })
    }

    try {
      if (typeof resolver == 'function')
        resolver(self['resolve'], self['reject'])
    } catch (e) {
      self['reject'](e)
    }

    return self

    // ref : reference to 'then' function
    // cb, ec, cn : successCallback, failureCallback, notThennableCallback
    function thennable (ref, cb, ec, cn) {
      // Promises can be rejected with other promises, which should pass through
      if (state == 2) {
        return cn()
      }
      if ((typeof val == 'object' || typeof val == 'function') && typeof ref == 'function') {
        try {

          // cnt protects against abuse calls from spec checker
          var cnt = 0
          ref.call(val, function (v) {
            if (cnt++) return
            val = v
            cb()
          }, function (v) {
            if (cnt++) return
            val = v
            ec()
          })
        } catch (e) {
          val = e
          ec()
        }
      } else {
        cn()
      }
    };

    function fire() {

      // check if it's a thenable
      var ref;
      try {
        ref = val && val.then
      } catch (e) {
        val = e
        state = 2
        return fire()
      }

      thennable(ref, function () {
        state = 1
        fire()
      }, function () {
        state = 2
        fire()
      }, function () {
        try {
          if (state == 1 && typeof fn == 'function') {
            val = fn(val)
          }

          else if (state == 2 && typeof er == 'function') {
            val = er(val)
            state = 1
          }
        } catch (e) {
          val = e
          return finish()
        }

        if (val == self) {
          val = TypeError()
          finish()
        } else thennable(ref, function () {
            finish(3)
          }, finish, function () {
            finish(state == 1 && 3)
          })

      })
    }


  }

  // Export our library object, either for node.js or as a globally scoped variable
  if (true) {
    module['exports'] = Deferred
  } else {}
})()

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(12), __webpack_require__(43).setImmediate))

/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __exportStar = (this && this.__exportStar) || function(m, exports) {
    for (var p in m) if (p !== "default" && !Object.prototype.hasOwnProperty.call(exports, p)) __createBinding(exports, m, p);
};
Object.defineProperty(exports, "__esModule", { value: true });
__exportStar(__webpack_require__(5), exports);
__exportStar(__webpack_require__(21), exports);
__exportStar(__webpack_require__(51), exports);
__exportStar(__webpack_require__(52), exports);
__exportStar(__webpack_require__(8), exports);
__exportStar(__webpack_require__(54), exports);
__exportStar(__webpack_require__(3), exports);
__exportStar(__webpack_require__(15), exports);
__exportStar(__webpack_require__(23), exports);
__exportStar(__webpack_require__(55), exports);
__exportStar(__webpack_require__(22), exports);
__exportStar(__webpack_require__(14), exports);
__exportStar(__webpack_require__(25), exports);


/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.DataProxy = void 0;
var ajax_1 = __webpack_require__(14);
var DataProxy = /** @class */ (function () {
    function DataProxy(url, config) {
        if (config === void 0) { config = {}; }
        this.url = this._url = url;
        this.config = config;
    }
    DataProxy.prototype.updateUrl = function (url, params) {
        if (params === void 0) { params = {}; }
        this._url = this.url = url || this._url;
        this.url += this.url.includes("?") ? "&" : "?";
        for (var param in params) {
            this.config[param] = params[param];
            this.url += "".concat(param, "=").concat(encodeURIComponent(params[param]), "&");
        }
        this.url = this.url.slice(0, -1);
    };
    DataProxy.prototype.load = function () {
        return ajax_1.ajax.get(this.url, undefined, this.config);
    };
    DataProxy.prototype.save = function (data, mode) {
        switch (mode) {
            case "delete":
                return ajax_1.ajax.delete(this.url, data, this.config);
            case "update":
                return ajax_1.ajax.put(this.url, data, this.config);
            case "insert":
            default:
                return ajax_1.ajax.post(this.url, data, this.config);
        }
    };
    return DataProxy;
}());
exports.DataProxy = DataProxy;


/***/ }),
/* 9 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.ChartEvents = void 0;
var ChartEvents;
(function (ChartEvents) {
    ChartEvents["toggleSeries"] = "toggleSeries";
    ChartEvents["chartMouseMove"] = "chartMouseMove";
    ChartEvents["chartMouseLeave"] = "chartMouseLeave";
    ChartEvents["resize"] = "resize";
    ChartEvents["serieClick"] = "serieClick";
    // private
    ChartEvents["seriaMouseMove"] = "seriaMouseMove";
    ChartEvents["seriaMouseLeave"] = "seriaMouseLeave";
})(ChartEvents || (exports.ChartEvents = ChartEvents = {}));


/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.radarScale = exports.pieLikeHandlers = exports.shiftCoordinates = exports.getCoordinates = void 0;
var dom_1 = __webpack_require__(1);
var common_1 = __webpack_require__(2);
function getCoordinates(percent, radiusX, radiusY, stroke) {
    if (stroke) {
        percent = percent + (2 * radiusX * Math.asin((0.5 * stroke) / radiusX)) / (2 * Math.PI * radiusX);
    }
    var x = Math.cos(2 * Math.PI * percent) * radiusX;
    var y = Math.sin(2 * Math.PI * percent) * radiusY;
    return [x, y];
}
exports.getCoordinates = getCoordinates;
function shiftCoordinates(item, dx, dy) {
    return [item[0] + dx, item[1] + dy];
}
exports.shiftCoordinates = shiftCoordinates;
function setTransform(elem, shiftX, shiftY) {
    elem.setAttribute("transform", "translate(".concat(shiftX, ", ").concat(shiftY, ") scale(1.05)"));
    elem.classList.add("dhx_pie-transform-delay");
}
function removeTransform(elem) {
    elem.setAttribute("transform", "translate(0, 0)");
    elem.classList.remove("dhx_pie-transform-delay");
}
exports.pieLikeHandlers = {
    onmouseover: function (shiftX, shiftY, _, node) {
        var id = node.parent.attrs.id;
        setTransform(node.el, shiftX, shiftY);
        node.parent.body.forEach(function (nodeEl) {
            if (nodeEl.attrs.id === "".concat(id, "-text") || nodeEl.attrs.id === "".concat(id, "-connector")) {
                setTransform(nodeEl.el, shiftX, shiftY);
            }
        });
    },
    onmouseout: function (_, node) {
        var id = node.parent.attrs.id;
        removeTransform(node.el);
        node.parent.body.forEach(function (nodeEl) {
            if (nodeEl.attrs.id === "".concat(id, "-text") || nodeEl.attrs.id === "".concat(id, "-connector")) {
                removeTransform(nodeEl.el);
            }
        });
    },
};
function checkMiss(v, r) {
    var miss = 0.000001;
    return v - miss < r && v + miss > r;
}
function drawBackgroundCircle(radius, color) {
    return (0, dom_1.sv)("circle", {
        cx: 0,
        cy: 0,
        r: radius,
        fill: color,
        stroke: "none",
        class: "background-circle",
    });
}
function arc(r, flag) {
    return "M".concat(-r, ",0A").concat(r, ",").concat(r, " 0 ").concat(flag ? 0 : 1, " 1 ").concat(r, ",0A").concat(r, ",").concat(r, " 0 ").concat(flag ? 0 : 1, " 1 ").concat(-r, ",0");
}
function radarScale(data, width, height) {
    var getScaleAriaAttrs = function (text) { return ({
        "aria-label": "x-axis".concat(text ? ", " + text : ""),
    }); };
    var radius;
    if (height > width) {
        radius = width / 2;
    }
    else {
        radius = height / 2;
    }
    var scalePercent = 1 / data.scales.length;
    var largeArcFlag = scalePercent > 0.5 ? 1 : 0;
    var svg = [];
    var background = drawBackgroundCircle(radius, "#fafafa");
    svg.push(background);
    var currentPercent = -0.25;
    var grid = [];
    var axis = data.axis;
    var gridClass = "radar-grid ".concat(data.zebra ? "zebra" : "");
    for (var i = 1; i < axis.length; i += 2) {
        var r1 = radius * axis[i - 1];
        var r2 = radius * axis[i];
        var d = "".concat(arc(r1, true), " ").concat(arc(r2, false));
        var arcs = (0, dom_1.sv)("path", {
            d: d,
            fill: "none",
            stroke: "black",
            class: gridClass,
        });
        grid.push(arcs);
    }
    svg.push(grid);
    data.scales.forEach(function (item) {
        var _a = getCoordinates(currentPercent, radius, radius), startX = _a[0], startY = _a[1];
        var nextPercent = currentPercent + scalePercent;
        var _b = getCoordinates(nextPercent, radius, radius), endX = _b[0], endY = _b[1];
        var d = "M ".concat(startX, " ").concat(startY, " A ").concat(radius, " ").concat(radius, " 0 ").concat(largeArcFlag, " 1 ").concat(endX, " ").concat(endY, " L 0 0");
        var path = (0, dom_1.sv)("path", {
            d: d,
            stroke: "black",
            fill: "none",
            class: "radar-scale",
        });
        svg.push(path);
        var _c = [8, 8], yTextPadding = _c[0], xTextPadding = _c[1];
        var dy = checkMiss(currentPercent, 0) || checkMiss(currentPercent, 0.5)
            ? 0
            : currentPercent < 0 || currentPercent > 0.5
                ? -yTextPadding
                : yTextPadding;
        var dx = checkMiss(currentPercent, -0.25) || checkMiss(currentPercent, 0.25)
            ? 0
            : currentPercent < -0.25 || currentPercent > 0.25
                ? -xTextPadding
                : xTextPadding;
        if (checkMiss(currentPercent, -0.25) || checkMiss(currentPercent, 0.25)) {
            var alignFn = checkMiss(currentPercent, -0.25) ? common_1.verticalTopText : common_1.verticalBottomText;
            var text = (0, dom_1.sv)("text", { x: startX + dx, y: startY + dy, class: "scale-text" }, [alignFn(item)]);
            svg.push(text);
        }
        else {
            var className = currentPercent >= -0.25 && currentPercent <= 0.25
                ? "start-text scale-text"
                : "end-text scale-text";
            var text = (0, dom_1.sv)("text", { x: startX + dx, y: startY + dy, class: className }, [
                (0, common_1.verticalCenteredText)(item),
            ]);
            svg.push(text);
        }
        currentPercent = nextPercent;
    });
    currentPercent = -0.25;
    if (data.realAxis) {
        var scaleText = data.realAxis.map(function (item, index) {
            var _a = getCoordinates(-0.25, radius * axis[index], radius * axis[index]), x = _a[0], y = _a[1];
            return (0, dom_1.sv)("text", { x: x, y: y, dx: -10, class: "radar-axis-text" }, [
                (0, common_1.verticalCenteredText)(item.toString()),
            ]);
        });
        svg.push(scaleText);
    }
    return (0, dom_1.sv)("g", __assign({ transform: "translate(".concat(width / 2, ", ").concat(height / 2, ")") }, getScaleAriaAttrs(data.attribute)), svg);
}
exports.radarScale = radarScale;


/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
var core_1 = __webpack_require__(0);
var types_1 = __webpack_require__(9);
var common_1 = __webpack_require__(2);
var line_1 = __webpack_require__(28);
var date_1 = __webpack_require__(29);
var BaseSeria = /** @class */ (function () {
    function BaseSeria(_data, config, other) {
        var _this = this;
        this._data = _data;
        this._handlers = {
            onclick: function (id, value) { return _this._events.fire(types_1.ChartEvents.serieClick, [id, value]); },
            onmousemove: function (id, value, e) {
                return _this._events.fire(types_1.ChartEvents.seriaMouseMove, [id, value, e]);
            },
            onmouseleave: function (id, value) {
                return _this._events.fire(types_1.ChartEvents.seriaMouseLeave, [id, value]);
            },
        };
        this.id = config.id = config.id || (0, core_1.uid)();
        this._events = other;
        this._points = [];
        this._setDefaults(config);
    }
    BaseSeria.prototype.toggle = function () {
        this.config.active = !this.config.active;
    };
    BaseSeria.prototype.getClosest = function (x, y) {
        var res = [Infinity, null, null, null];
        for (var _i = 0, _a = this._points; _i < _a.length; _i++) {
            var point = _a[_i];
            var dist = this._getClosestDist(x, y, point[0], point[1]);
            if (res[0] > dist) {
                res[0] = dist;
                res[1] = point[0];
                res[2] = point[1];
                res[3] = point[2];
            }
        }
        return res;
    };
    BaseSeria.prototype.getClosestVertical = function (x) {
        var res = [Infinity, null, null, null, null];
        for (var _i = 0, _a = this._points; _i < _a.length; _i++) {
            var point = _a[_i];
            var dist = Math.abs(point[0] - x);
            if (res[0] > dist) {
                res[0] = dist;
                res[1] = point[0];
                res[2] = point[1];
                res[3] = point[2];
                res[4] = point[4];
            }
        }
        return res;
    };
    BaseSeria.prototype.getTooltipType = function (_id) {
        return "top";
    };
    BaseSeria.prototype.getTooltipText = function (id) {
        if (!this._data.getItem(id) && this.config.type !== "calendarHeatMap") {
            return;
        }
        if (this.config.tooltip) {
            var p = void 0;
            var heatP = void 0;
            if (this.config.type === "calendarHeatMap") {
                heatP = this._points.find(function (i) { return i[2] === id.toString(); });
                if (heatP) {
                    var heatDate = new Date();
                    heatDate.setTime(heatP[0]);
                    heatP = [
                        (0, date_1.getFormattedDate)(this.config.dateFormat, heatDate),
                        heatP[1],
                    ];
                }
            }
            else {
                p = this._defaultLocator(this._data.getItem(id));
            }
            if (this.config.tooltipTemplate) {
                return this.config.tooltipTemplate(p || heatP);
            }
            return p ? p[0] : "".concat(heatP[1], ", <br>").concat(heatP[0]);
        }
    };
    BaseSeria.prototype.dataReady = function (prev) {
        return (this._points = []);
    };
    BaseSeria.prototype.paint = function (width, height) {
        return this._calckFinalPoints(width, height);
    };
    BaseSeria.prototype.getPoints = function () {
        return this._points;
    };
    BaseSeria.prototype.addScale = function (type, scale) {
        // do nothing
    };
    BaseSeria.prototype._getClosestDist = function (x, y, px, py) {
        return (0, common_1.euclideanDistance)(x, y, px, py);
    };
    BaseSeria.prototype._calckFinalPoints = function (_width, _height) {
        // do nothing
    };
    BaseSeria.prototype._setDefaults = function (config) {
        this.config = config;
    };
    BaseSeria.prototype._defaultLocator = function (_) {
        return [null, null];
    };
    BaseSeria.prototype._getPointType = function (form, color) {
        return (0, line_1.getShadeHelper)(form, color);
    };
    return BaseSeria;
}());
exports.default = BaseSeria;


/***/ }),
/* 12 */
/***/ (function(module, exports) {

var g;

// This works in non-strict mode
g = (function() {
	return this;
})();

try {
	// This works if eval is allowed (see CSP)
	g = g || new Function("return this")();
} catch (e) {
	// This works if the window reference is available
	if (typeof window === "object") g = window;
}

// g can still be undefined, but nothing to do about it...
// We return undefined, instead of nothing here, so it's
// easier to handle this case. if(!global) { ...}

module.exports = g;


/***/ }),
/* 13 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.EventsMixin = exports.EventSystem = void 0;
var EventSystem = /** @class */ (function () {
    function EventSystem(context) {
        this.events = {};
        this.context = context || this;
    }
    EventSystem.prototype.on = function (name, callback, context) {
        var event = name.toLowerCase();
        this.events[event] = this.events[event] || [];
        this.events[event].push({ callback: callback, context: context || this.context });
    };
    EventSystem.prototype.detach = function (name, context) {
        var event = name.toLowerCase();
        var eStack = this.events[event];
        if (context && eStack && eStack.length) {
            for (var i = eStack.length - 1; i >= 0; i--) {
                if (eStack[i].context === context) {
                    eStack.splice(i, 1);
                }
            }
        }
        else {
            this.events[event] = [];
        }
    };
    EventSystem.prototype.fire = function (name, args) {
        if (typeof args === "undefined") {
            args = [];
        }
        var event = name.toLowerCase();
        if (this.events[event]) {
            var res = this.events[event].map(function (e) { return e.callback.apply(e.context, args); });
            return !res.includes(false);
        }
        return true;
    };
    EventSystem.prototype.clear = function () {
        this.events = {};
    };
    return EventSystem;
}());
exports.EventSystem = EventSystem;
function EventsMixin(obj) {
    obj = obj || {};
    var eventSystem = new EventSystem(obj);
    obj.detachEvent = eventSystem.detach.bind(eventSystem);
    obj.attachEvent = eventSystem.on.bind(eventSystem);
    obj.callEvent = eventSystem.fire.bind(eventSystem);
}
exports.EventsMixin = EventsMixin;


/***/ }),
/* 14 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(Promise) {
Object.defineProperty(exports, "__esModule", { value: true });
exports.ajax = void 0;
var types_1 = __webpack_require__(5);
var helpers_1 = __webpack_require__(3);
function toQueryString(data) {
    return Object.keys(data)
        .reduce(function (entries, key) {
        var value = typeof data[key] === "object" ? JSON.stringify(data[key]) : data[key];
        entries.push(key + "=" + encodeURIComponent(value));
        return entries;
    }, [])
        .join("&");
}
function inferResponseType(contentType) {
    if (!contentType) {
        return "text";
    }
    if (contentType.includes("json")) {
        return "json";
    }
    if (contentType.includes("xml")) {
        return "xml";
    }
    return "text";
}
function send(url, data, method, headers, responseType) {
    function parseResponse(responseText, genResponseType) {
        switch (genResponseType) {
            case "json": {
                return JSON.parse(responseText);
            }
            case "text": {
                return responseText;
            }
            case "xml": {
                var driver = (0, helpers_1.toDataDriver)(types_1.DataDriver.xml);
                if (driver) {
                    return driver.toJsonObject(responseText);
                }
                else {
                    return { parseError: "Incorrect data driver type: 'xml'" };
                }
            }
            default: {
                return responseText;
            }
        }
    }
    var allHeaders = headers || {};
    if (responseType) {
        allHeaders.Accept = "application/" + responseType;
    }
    if (method !== "GET") {
        allHeaders["Content-Type"] = allHeaders["Content-Type"] || "application/json";
    }
    if (method === "GET") {
        var urlData = data && typeof data === "object"
            ? toQueryString(data)
            : data && typeof data === "string"
                ? data
                : "";
        if (urlData) {
            url += !url.includes("?") ? "?" : "&";
            url += urlData;
        }
        data = null;
    }
    if (!window.fetch) {
        return new Promise(function (resolve, reject) {
            var xhr = new XMLHttpRequest();
            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                    if (responseType === "raw") {
                        resolve({
                            url: xhr.responseURL,
                            headers: xhr
                                .getAllResponseHeaders()
                                .trim()
                                .split(/[\r\n]+/)
                                .reduce(function (acc, cur) {
                                var kv = cur.split(": ");
                                acc[kv[0]] = kv[1];
                                return acc;
                            }, {}),
                            body: xhr.response,
                        });
                    }
                    if (xhr.status === 204) {
                        resolve();
                    }
                    else {
                        resolve(parseResponse(xhr.responseText, responseType || inferResponseType(xhr.getResponseHeader("Content-Type"))));
                    }
                }
                else {
                    reject({
                        status: xhr.status,
                        statusText: xhr.statusText,
                    });
                }
            };
            xhr.onerror = function () {
                reject({
                    status: xhr.status,
                    statusText: xhr.statusText,
                    message: xhr.responseText,
                });
            };
            xhr.open(method, url);
            for (var headerKey in allHeaders) {
                xhr.setRequestHeader(headerKey, allHeaders[headerKey]);
            }
            switch (method) {
                case "POST":
                case "DELETE":
                case "PUT":
                    xhr.send(data !== undefined ? JSON.stringify(data) : "");
                    break;
                case "GET":
                    xhr.send();
                    break;
                default:
                    xhr.send();
                    break;
            }
        });
    }
    else {
        var isJson = allHeaders["Content-Type"] === "application/json";
        if (isJson && data && typeof data === "object") {
            data = JSON.stringify(data);
        }
        return window
            .fetch(url, {
            method: method,
            body: data || null,
            headers: allHeaders,
        })
            .then(function (response) {
            if (response.ok) {
                var genResponseType = responseType || inferResponseType(response.headers.get("Content-Type"));
                if (genResponseType === "raw") {
                    return {
                        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
                        // @ts-ignore
                        headers: Object.fromEntries(response.headers.entries()),
                        url: response.url,
                        body: response.body,
                    };
                }
                if (response.status !== 204) {
                    switch (genResponseType) {
                        case "json": {
                            return response.json();
                        }
                        case "xml": {
                            var driver_1 = (0, helpers_1.toDataDriver)(types_1.DataDriver.xml);
                            if (driver_1) {
                                return response.text().then(function (xmlData) { return driver_1.toJsonObject(xmlData); });
                            }
                            else {
                                return response.text();
                            }
                        }
                        default:
                            return response.text();
                    }
                }
            }
            else {
                return response.text().then(function (message) {
                    return Promise.reject({
                        status: response.status,
                        statusText: response.statusText,
                        message: message,
                    });
                });
            }
        });
    }
}
exports.ajax = {
    get: function (url, data, config) {
        return send(url, data, "GET", config && config.headers, config !== undefined ? config.responseType : undefined);
    },
    post: function (url, data, config) {
        return send(url, data, "POST", config && config.headers, config !== undefined ? config.responseType : undefined);
    },
    put: function (url, data, config) {
        return send(url, data, "PUT", config && config.headers, config !== undefined ? config.responseType : undefined);
    },
    delete: function (url, data, config) {
        return send(url, data, "DELETE", config && config.headers, config !== undefined ? config.responseType : undefined);
    },
};

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(6)))

/***/ }),
/* 15 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.CsvDriver = void 0;
var helpers_1 = __webpack_require__(3);
var CsvDriver = /** @class */ (function () {
    function CsvDriver(config) {
        var initConfig = {
            skipHeader: 0,
            nameByHeader: false,
            rowDelimiter: "\n",
            columnDelimiter: ",",
        };
        this.config = __assign(__assign({}, initConfig), config);
        if (this.config.nameByHeader) {
            this.config.skipHeader = 1;
        }
    }
    CsvDriver.prototype.getFields = function (row, headers) {
        var parts = (0, helpers_1.splitCsv)(row, this.config.columnDelimiter).map(function (part) {
            return part.replace(/^"(.*)"$/s, "$1").replace(/""/g, '"');
        });
        var obj = {};
        for (var i = 0; i < parts.length; i++) {
            obj[headers ? headers[i] : i + 1] = isNaN(Number(parts[i])) ? parts[i] : parseFloat(parts[i]);
        }
        return obj;
    };
    CsvDriver.prototype.getRows = function (data) {
        return (0, helpers_1.splitCsv)(data, this.config.rowDelimiter);
    };
    CsvDriver.prototype.toJsonArray = function (data) {
        var _this = this;
        var rows = this.getRows(data);
        var names = this.config.names;
        if (this.config.skipHeader) {
            var top_1 = rows.splice(0, this.config.skipHeader);
            if (this.config.nameByHeader) {
                names = top_1[0].trim().split(this.config.columnDelimiter);
            }
        }
        return rows.map(function (row) { return _this.getFields(row, names); });
    };
    CsvDriver.prototype.serialize = function (data, withoutHeader) {
        var header = data[0]
            ? Object.keys(data[0])
                .filter(function (key) { return !key.startsWith("$"); })
                .join(this.config.columnDelimiter) + this.config.rowDelimiter
            : "";
        var readyData = this._serialize(data);
        if (withoutHeader) {
            return readyData;
        }
        return header + readyData;
    };
    CsvDriver.prototype._serialize = function (data) {
        var _this = this;
        return data.reduce(function (csv, row) {
            var keys = Object.keys(row);
            var cells = keys.reduce(function (total, key, i) {
                var _a, _b;
                if (key.startsWith("$") || key === "items") {
                    return total;
                }
                var value = (_b = (_a = row[key]) === null || _a === void 0 ? void 0 : _a.toString()) !== null && _b !== void 0 ? _b : "";
                var normalizedValue = new RegExp("[".concat(_this.config.columnDelimiter, "\"\n]")).test(value)
                    ? "\"".concat(value.replace(/"/g, '""').replace(/\n/g, "\\n"), "\"")
                    : value;
                var delimiter = i === keys.length - 1 || keys[i + 1].startsWith("$") ? "" : _this.config.columnDelimiter;
                return total + normalizedValue + delimiter;
            }, "");
            if (row.items) {
                return "".concat(csv).concat(csv ? "\n" : "").concat(cells).concat(_this._serialize(row.items));
            }
            return "".concat(csv).concat(csv ? _this.config.rowDelimiter : "").concat(cells);
        }, "");
    };
    return CsvDriver;
}());
exports.CsvDriver = CsvDriver;


/***/ }),
/* 16 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Scale = void 0;
var AxisCreator_1 = __webpack_require__(65);
var SvgScales_1 = __webpack_require__(26);
var common_1 = __webpack_require__(2);
var renderScale = {
    left: SvgScales_1.left,
    right: SvgScales_1.right,
    bottom: SvgScales_1.bottom,
    top: SvgScales_1.top,
};
var renderGrid = {
    left: SvgScales_1.leftGrid,
    right: SvgScales_1.rightGrid,
    bottom: SvgScales_1.bottomGrid,
    top: SvgScales_1.topGrid,
};
var Scale = /** @class */ (function () {
    function Scale(_data, config, position) {
        this._data = _data;
        this._padding = false;
        this._charts = [];
        this._position = position;
        this._setDefaults(config);
        this._isXDirection = position === "bottom" || position === "top";
        if (position !== "radial") {
            if (position === "left" || position === "right") {
                this.config.size = this.config.size || 40 + (this.config.title ? 40 : 0);
            }
            else {
                this.config.size = this.config.size || 20 + (this.config.title ? 40 : 0);
            }
        }
    }
    Scale.prototype.addPadding = function () {
        this._padding = true;
    };
    Scale.prototype.getSize = function () {
        return this.config.size;
    };
    Scale.prototype.scaleReady = function (sizes) {
        var points = [];
        this._charts.forEach(function (chart) {
            chart.getPoints().forEach(function (item) { return points.push(item[1]); }); // y-value
        });
        this._axis = new AxisCreator_1.AxisCreator(points, this.config).getScale();
        var position = this._position;
        if (position !== "radial") {
            sizes[position] += this.config.size;
        }
    };
    Scale.prototype.point = function (pos) {
        if (this.config.log) {
            return this._logPoint(pos);
        }
        else {
            return this._isXDirection
                ? (pos - this._axis.min) / (this._axis.max - this._axis.min)
                : 1 - (pos - this._axis.min) / (this._axis.max - this._axis.min);
        }
    };
    Scale.prototype.add = function (val) {
        this._charts.push(val);
    };
    Scale.prototype.paint = function (width, height) {
        var _this = this;
        if (this.config.hidden) {
            return null;
        }
        var steps = this._axis.steps;
        var points = steps.map(function (item) { return [
            _this._isXDirection ? _this.point(item) * width : _this.point(item) * height,
            item,
        ]; });
        if (points.length === 0 && this._position === "left") {
            points = [[0, 0]];
        }
        return renderScale[this._position](points, this.config, width, height);
    };
    Scale.prototype.scaleGrid = function () {
        var _this = this;
        var getPoints = function (width, height) {
            return _this._axis.steps.map(function (item) { return [
                _this._isXDirection ? _this.point(item) * width : _this.point(item) * height,
                item,
            ]; });
        };
        var type = this._position;
        var grid = this.config.grid;
        var dashed = this.config.dashed;
        var hidden = this.config.hidden;
        var getSpecificLevel = function () { return _this._axis.steps.indexOf(_this.config.targetLine); };
        var getSpecificNumber = function () { return _this.point(_this.config.targetValue); };
        return {
            paint: function (width, height) {
                var targetLine = getSpecificLevel();
                var points = getPoints(width, height);
                var targetValue = getSpecificNumber();
                var config = {
                    targetLine: targetLine,
                    dashed: dashed,
                    grid: grid,
                    targetValue: targetValue,
                    hidden: hidden,
                };
                return renderGrid[type](points, width, height, config);
            },
        };
    };
    Scale.prototype._setDefaults = function (config) {
        var defaults = {
            scalePadding: 20,
            textPadding: 11,
            grid: true,
            targetLine: null,
            showText: true,
        };
        if (config.locator) {
            this.locator = (0, common_1.locator)(config.locator);
        }
        this.config = __assign(__assign({}, defaults), config);
    };
    Scale.prototype._logPoint = function (pos) {
        var logPos;
        var sign = Math.abs(pos) / pos;
        var steps = this._axis.steps;
        var count = steps.length - 1;
        var index = steps.indexOf(pos);
        if (index !== -1) {
            logPos = index / count;
        }
        else {
            var dx = this._axis.min < 0 ? steps.indexOf(0) : 0;
            var exp = sign * (0, common_1.log10)(Math.abs(pos));
            logPos = (dx + exp) / count;
        }
        return this._isXDirection ? logPos : 1 - logPos;
    };
    return Scale;
}());
exports.Scale = Scale;


/***/ }),
/* 17 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
Object.defineProperty(exports, "__esModule", { value: true });
var common_1 = __webpack_require__(2);
var BaseSeria_1 = __webpack_require__(11);
var ScaleSeria = /** @class */ (function (_super) {
    __extends(ScaleSeria, _super);
    function ScaleSeria() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    ScaleSeria.prototype.addScale = function (type, scale) {
        if (type === "bottom" || type === "top") {
            this.xScale = scale;
            this._xLocator = scale.locator;
        }
        else {
            this.yScale = scale;
            this._yLocator = (0, common_1.locator)(this.config.value);
        }
    };
    ScaleSeria.prototype.paint = function (width, height) {
        _super.prototype.paint.call(this, width, height);
    };
    ScaleSeria.prototype.dataReady = function (prev) {
        var _this = this;
        if (!this.config.active) {
            return (this._points = []);
        }
        this._points = this._data.map(function (item, index) {
            // raw values
            var x = _this._xLocator(item);
            var y = _this._yLocator(item) || 0;
            var set = [x, y, item.id, x, y];
            if (prev) {
                set[1] += prev[index][1];
            }
            return set;
        });
        return this._points;
    };
    ScaleSeria.prototype._calckFinalPoints = function (width, height) {
        var _this = this;
        this._points.forEach(function (item, index) {
            // scaled values
            item[0] = _this.xScale.point(item[0]) * width || 0;
            var pointHeight = _this.yScale.point(item[1]) * height;
            item[1] = !isNaN(pointHeight) ? pointHeight : _this.config.type === "xbar" ? 0 : height;
        });
    };
    ScaleSeria.prototype._defaultLocator = function (v) {
        return [this._yLocator(v), this._xLocator(v)];
    };
    ScaleSeria.prototype._getCss = function () {
        return "chart ".concat(this.config.type, " ").concat(this.config.css || "", " ").concat(this.config.dashed ? "dash-line" : "");
    };
    return ScaleSeria;
}(BaseSeria_1.default));
exports.default = ScaleSeria;


/***/ }),
/* 18 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
var common_1 = __webpack_require__(2);
var BaseSeria_1 = __webpack_require__(11);
var NoScaleSeria = /** @class */ (function (_super) {
    __extends(NoScaleSeria, _super);
    function NoScaleSeria() {
        var _this = _super !== null && _super.apply(this, arguments) || this;
        _this._center = [0, 0]; // (x, y)
        _this._tooltipData = []; // (x, y)
        return _this;
    }
    NoScaleSeria.prototype.scaleReady = function (sizes) {
        for (var key in sizes) {
            sizes[key] += this.config.paddings;
        }
        return sizes;
    };
    NoScaleSeria.prototype.dataReady = function () {
        var _this = this;
        var data = this._data;
        this._sum = data.reduce(function (sum, item) { return (item.$hidden ? sum : sum + parseFloat(_this._valueLocator(item))); }, 0);
        this._points = data.reduce(function (items, item, i) {
            if (item.$hidden) {
                return items;
            }
            var t = _this._textLocator(item);
            var v = _this._valueLocator(item);
            var x = v / _this._sum;
            var c = _this._colorLocator ? _this._colorLocator(item) : (0, common_1.getDefaultColor)(i);
            items.push([x, v, item.id, t, c]);
            return items;
        }, []);
        return this._points;
    };
    NoScaleSeria.prototype.toggle = function (id) {
        var item = this._data.getItem(id);
        if (!item) {
            return;
        }
        this._data.update(id, { $hidden: !item.$hidden });
    };
    NoScaleSeria.prototype.getClosest = function (x, y) {
        var percent = 1 - (Math.atan2(x - this._center[0], y - this._center[1]) + Math.PI) / Math.PI / 2;
        var points = this._points;
        for (var i = 0; i < points.length; i++) {
            if (points[i][0] >= percent) {
                return [0, this._tooltipData[i][0], this._tooltipData[i][1], points[i][2]];
            }
            percent -= points[i][0];
        }
        return [Infinity, null, null, null];
    };
    NoScaleSeria.prototype.getTooltipText = function (id) {
        if (this.config.tooltip) {
            var p = this._defaultLocator(this._data.getItem(id));
            if (this.config.tooltipTemplate) {
                return this.config.tooltipTemplate(p);
            }
            return p[0];
        }
    };
    NoScaleSeria.prototype.getTooltipType = function (_id) {
        return "simple";
    };
    NoScaleSeria.prototype._setDefaults = function (config) {
        var _this = this;
        var defaults = {
            subType: "basic",
            paddings: config.useLines ? 70 : 50,
        };
        this.config = __assign(__assign({}, defaults), config);
        this._drawPointType = this._getPointType("empty", "none");
        this._valueLocator = (0, common_1.locator)(config.value);
        this._textLocator = (0, common_1.locator)(config.text);
        if (config.color) {
            this._colorLocator = (0, common_1.locator)(config.color);
        }
        else if (config.monochrome) {
            this._colorLocator = function (item) { return (0, common_1.getColorShade)(config.monochrome, _this._getPercent(item) * 2); }; // 2 for more bright
        }
    };
    NoScaleSeria.prototype._defaultLocator = function (v) {
        return [this._valueLocator(v), this._textLocator(v)];
    };
    NoScaleSeria.prototype._getPercent = function (item) {
        return parseFloat(this._valueLocator(item)) / this._sum;
    };
    return NoScaleSeria;
}(BaseSeria_1.default));
exports.default = NoScaleSeria;


/***/ }),
/* 19 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
var dom_1 = __webpack_require__(1);
var common_1 = __webpack_require__(2);
var ScaleSeria_1 = __webpack_require__(17);
var Line = /** @class */ (function (_super) {
    __extends(Line, _super);
    function Line() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    Line.prototype.paint = function (width, height) {
        var _this = this;
        _super.prototype.paint.call(this, width, height);
        var getChartAriaAttrs = function (cfg) { return ({
            "aria-label": "chart ".concat(cfg.value || ""),
        }); };
        var getPointAriaAttrs = function (key, points) {
            var point;
            if (key) {
                point = points.find(function (p) { return key.includes(p[2]); });
            }
            return {
                role: "graphics-symbol",
                "aria-roledescription": "point",
                "aria-label": point ? "point x=".concat(point[3], " y=").concat(point[4]) : "",
                tabindex: 0,
            };
        };
        var color = this.config.pointColor || this.config.color;
        var svg = [];
        if (this.config.strokeWidth) {
            svg.push(this._getForm(this._points, this.config, width, height));
        }
        if (this.config.pointType) {
            var point_1 = this._getPointType(this.config.pointType, color);
            svg = svg.concat(this._points
                .map(function (p) { return point_1(p[0], p[1], (0, common_1.calcPointRef)(p[2], _this.id)); })
                .map(function (node, index) {
                if (node && node.attrs) {
                    node.attrs = __assign(__assign({}, node.attrs), getPointAriaAttrs(node.key, _this._points));
                    if (_this.config.tooltip) {
                        node.attrs.onmousemove = [
                            _this._handlers.onmousemove,
                            _this._points[index][2],
                            _this.id,
                        ];
                        node.attrs.onmouseleave = [
                            _this._handlers.onmouseleave,
                            _this._points[index][2],
                            _this.id,
                        ];
                        node.attrs.onclick = [
                            _this._handlers.onmousemove,
                            _this._points[index][2],
                            _this.id,
                        ];
                    }
                }
                return node;
            }));
        }
        return (0, dom_1.sv)("g", __assign(__assign({ class: "seria", _key: this.id }, getChartAriaAttrs(this.config)), { tabindex: 0 }), svg);
    };
    Line.prototype._getForm = function (points, config, width, height) {
        var d = points.map(function (item, index) { return (index ? "L" : "M") + "".concat(item[0], " ").concat(item[1]); }).join(" ");
        var path = (0, dom_1.sv)("path", {
            id: "seria" + config.id,
            d: d,
            stroke: config.color,
            class: this._getCss(),
            "stroke-width": this.config.strokeWidth,
            fill: "none",
        });
        return path;
    };
    Line.prototype._setDefaults = function (config) {
        var defaults = {
            alpha: 1,
            strokeWidth: 2,
            active: true,
            tooltip: true,
        };
        this.config = __assign(__assign({}, defaults), config);
    };
    return Line;
}(ScaleSeria_1.default));
exports.default = Line;


/***/ }),
/* 20 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.MessageContainerPosition = exports.Position = exports.RealPosition = void 0;
var RealPosition;
(function (RealPosition) {
    RealPosition["left"] = "left";
    RealPosition["right"] = "right";
    RealPosition["top"] = "top";
    RealPosition["bottom"] = "bottom";
    RealPosition["center"] = "center";
})(RealPosition || (exports.RealPosition = RealPosition = {}));
var Position;
(function (Position) {
    Position["right"] = "right";
    Position["bottom"] = "bottom";
    Position["center"] = "center";
    Position["left"] = "left";
    Position["top"] = "top";
})(Position || (exports.Position = Position = {}));
var MessageContainerPosition;
(function (MessageContainerPosition) {
    MessageContainerPosition["topLeft"] = "top-left";
    MessageContainerPosition["topRight"] = "top-right";
    MessageContainerPosition["bottomLeft"] = "bottom-left";
    MessageContainerPosition["bottomRight"] = "bottom-right";
})(MessageContainerPosition || (exports.MessageContainerPosition = MessageContainerPosition = {}));


/***/ }),
/* 21 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.DataCollection = void 0;
var events_1 = __webpack_require__(13);
var loader_1 = __webpack_require__(47);
var sort_1 = __webpack_require__(49);
var dataproxy_1 = __webpack_require__(8);
var helpers_1 = __webpack_require__(3);
var types_1 = __webpack_require__(5);
var core_1 = __webpack_require__(0);
var group_1 = __webpack_require__(50);
var CsvDriver_1 = __webpack_require__(15);
var XMLDriver_1 = __webpack_require__(24);
var DataCollection = /** @class */ (function () {
    function DataCollection(config, events) {
        var _this = this;
        this._filters = {};
        this._sortingStates = [];
        this._changes = { order: [] };
        this.config = config || {};
        this._group = new group_1.Group();
        this._sort = new sort_1.Sort();
        this._loader = new loader_1.Loader(this, this._changes);
        this.events = events || new events_1.EventSystem(this);
        this.events.on(types_1.DataEvents.dataRequest, function (from, to) {
            var proxy = _this.dataProxy;
            if (proxy && proxy.updateUrl) {
                proxy.updateUrl(null, { from: from, limit: proxy.config.limit || to - from });
                _this.load(proxy);
            }
        });
        this.events.on(types_1.DataEvents.loadError, function (response) {
            setTimeout(function () {
                if (typeof response !== "string") {
                    (0, helpers_1.dhxError)(response);
                }
                else {
                    (0, helpers_1.dhxWarning)(response);
                }
            }, 0);
        });
        this._reset();
    }
    DataCollection.prototype._reset = function (config) {
        if (config === void 0) { config = {}; }
        if (!config.grouping)
            this.ungroup();
        this._order = [];
        this._pull = {};
        this._changes = { order: [] };
        this._initFilterOrder = this._initSortOrder = null;
        this._meta = new WeakMap();
        this._loaded = false;
    };
    DataCollection.prototype.group = function (order, config) {
        if (config === void 0) { config = {}; }
        if (!order) {
            (0, helpers_1.dhxError)("The group method has mandatory arguments");
        }
        if (!Array.isArray(order)) {
            (0, helpers_1.dhxError)("The group method expects an array as an argument");
        }
        if (!order.length) {
            (0, helpers_1.dhxError)("The array with the group method values cannot be empty");
        }
        if (this.isGrouped())
            this.ungroup();
        var groupConfig = this._group.getGroupConfig(config);
        if (!this.events.fire(types_1.DataEvents.beforeGroup, [groupConfig])) {
            return;
        }
        this._parse(this._group.group(order, (config === null || config === void 0 ? void 0 : config.data) || this._order, config), types_1.DataDriver.json, true);
        this.events.fire(types_1.DataEvents.afterGroup, [this._group.getGroupedFields(), groupConfig]);
    };
    DataCollection.prototype.ungroup = function () {
        if (!this.isGrouped()) {
            return;
        }
        var grouped = this._group.getGroupedFields();
        var groupConfig = this._group.getGroupConfig();
        if (!this.events.fire(types_1.DataEvents.beforeUnGroup, [grouped, groupConfig])) {
            return;
        }
        this._parse(this._group.ungroup(this._order));
        this.events.fire(types_1.DataEvents.afterUnGroup, [grouped, groupConfig]);
    };
    DataCollection.prototype.isGrouped = function () {
        return this._group.isGrouped();
    };
    DataCollection.prototype.add = function (newItem, index) {
        var _this = this;
        if (!this.events.fire(types_1.DataEvents.beforeAdd, [newItem])) {
            return;
        }
        var out;
        if (Array.isArray(newItem)) {
            out = newItem.map(function (element, key) {
                if (key !== 0 && index >= 0) {
                    index = index + 1;
                }
                return _this._add((0, core_1.copy)(element), index);
            });
        }
        else {
            out = this._add((0, core_1.copy)(newItem), index);
        }
        this._reapplyFilters();
        return out;
    };
    DataCollection.prototype.remove = function (id) {
        var _this = this;
        if (id instanceof Array) {
            __spreadArray([], id, true).map(function (elementId) {
                _this._remove(elementId);
            });
        }
        else if ((0, core_1.isId)(id)) {
            this._remove(id);
        }
    };
    DataCollection.prototype.removeAll = function () {
        this._reset();
        this.events.fire(types_1.DataEvents.removeAll);
        this.events.fire(types_1.DataEvents.change);
    };
    DataCollection.prototype.exists = function (id) {
        return !!this._pull[id];
    };
    DataCollection.prototype.getNearId = function (id) {
        var _a;
        var item = this._pull[id];
        if (!item) {
            return ((_a = this._order[0]) === null || _a === void 0 ? void 0 : _a.id) || "";
        }
    };
    DataCollection.prototype.getItem = function (id) {
        return this._pull[id];
    };
    DataCollection.prototype.update = function (id, newItem, silent) {
        var item = this.getItem(id);
        if (item) {
            if ((0, helpers_1.isEqualObj)(newItem, item)) {
                return;
            }
            if ((0, core_1.isId)(newItem.id) && id !== newItem.id) {
                (0, helpers_1.dhxWarning)("this method doesn't allow changing the id");
                if ((0, helpers_1.isDebug)()) {
                    // eslint-disable-next-line no-debugger
                    debugger;
                }
            }
            else {
                if (newItem.parent && item.parent && newItem.parent !== item.parent) {
                    this.move(id, -1, this, newItem.parent);
                }
                (0, core_1.extend)(this._pull[id], newItem, false);
                if (this.config.update) {
                    this.config.update(this._pull[id]);
                }
                if (!silent) {
                    this._onChange("update", id, this._pull[id]);
                }
            }
            this._reapplyFilters();
        }
        else {
            (0, helpers_1.dhxWarning)("item not found");
        }
    };
    DataCollection.prototype.getIndex = function (id) {
        if (!(0, core_1.isId)(id) || !(0, core_1.isDefined)(this._pull[id])) {
            return -1;
        }
        return this._order.findIndex(function (i) { return (i === null || i === void 0 ? void 0 : i.id) == id; });
    };
    DataCollection.prototype.getId = function (index) {
        if (!this._order[index]) {
            return;
        }
        return this._order[index].id;
    };
    DataCollection.prototype.getLength = function () {
        return this._order.length;
    };
    DataCollection.prototype.isDataLoaded = function (from, to) {
        if (from === void 0) { from = 0; }
        if (to === void 0) { to = this._order.length; }
        if ((0, core_1.isNumeric)(from) && (0, core_1.isNumeric)(to)) {
            return this._order.slice(from, to).filter(function (item) { return item && item.$empty; }).length === 0;
        }
        // if check succeeds once, collection can't go back to not-loaded state
        if (!this._loaded) {
            this._loaded = !this.find(function (item) { return item.$empty; });
        }
        return !!this._loaded;
    };
    DataCollection.prototype.filter = function (rule, config, silent) {
        var _a;
        if (config === null || config === void 0 ? void 0 : config.$restore) {
            rule = this._normalizeFilters(rule || this._filters);
        }
        if (!(config === null || config === void 0 ? void 0 : config.add)) {
            this._order = this._initFilterOrder || this._order;
            this._initFilterOrder = null;
            if (!(config === null || config === void 0 ? void 0 : config.$restore)) {
                for (var key in this._filters) {
                    var filter = this._filters[key];
                    if ((_a = filter.config) === null || _a === void 0 ? void 0 : _a.permanent) {
                        this._applyFilters(filter.rule);
                    }
                    else {
                        delete this._filters[key];
                    }
                }
            }
        }
        var id;
        if (rule && !(config === null || config === void 0 ? void 0 : config.$restore)) {
            id = (config === null || config === void 0 ? void 0 : config.id) || (0, core_1.uid)();
            this._filters[id] = { rule: rule, config: config || {} };
        }
        if (rule && typeof rule !== "function") {
            if ((0, core_1.isDefined)(rule.by)) {
                this._applyFilters(rule);
            }
            else {
                for (var key in rule) {
                    this._applyFilters(rule[key]);
                }
            }
        }
        else {
            this._applyFilters(rule);
        }
        if (!silent) {
            var filters = this._getPureFilters(this._filters);
            this.events.fire(types_1.DataEvents.filter, [(0, core_1.isEmptyObj)(filters) ? null : filters]);
        }
        return id;
    };
    DataCollection.prototype.resetFilter = function (config, silent) {
        var _a;
        var _b = config || {}, id = _b.id, permanent = _b.permanent;
        if ((0, core_1.isEmptyObj)(config)) {
            for (var key in this._filters) {
                if (!((_a = this._filters[key].config) === null || _a === void 0 ? void 0 : _a.permanent)) {
                    delete this._filters[key];
                }
            }
        }
        else if (permanent) {
            this._filters = {};
        }
        else if (id) {
            delete this._filters[id];
        }
        this.filter(null, { $restore: true }, silent);
        return (0, core_1.isEmptyObj)(this._getPureFilters(this._filters));
    };
    DataCollection.prototype.getFilters = function (config) {
        var filters = this.getRawFilters(config);
        var pureFilters = filters ? this._getPureFilters(filters) : {};
        return (0, core_1.isEmptyObj)(pureFilters) ? null : pureFilters;
    };
    DataCollection.prototype.getRawFilters = function (config) {
        var filters = this._filters;
        if (config === null || config === void 0 ? void 0 : config.permanent) {
            filters = Object.keys(filters).reduce(function (obj, key) {
                var _a;
                if ((_a = filters[key].config) === null || _a === void 0 ? void 0 : _a.permanent) {
                    obj[key] = filters[key];
                }
                return obj;
            }, {});
        }
        return (0, core_1.isEmptyObj)(filters) ? null : filters;
    };
    DataCollection.prototype.find = function (conf) {
        var data = this._initFilterOrder || this._order;
        for (var i = 0; i < data.length; i++) {
            var res = (0, helpers_1.findByConf)(data[i], conf, i, data);
            if (res) {
                return res;
            }
        }
        return null;
    };
    DataCollection.prototype.findAll = function (conf) {
        var data = this._initFilterOrder || this._order;
        var res = [];
        for (var i = 0; i < data.length; i++) {
            var item = (0, helpers_1.findByConf)(data[i], conf, i, data);
            if (item) {
                res.push(item);
            }
        }
        return res;
    };
    DataCollection.prototype.sort = function (rule, config, ignore) {
        var _this = this;
        var _a, _b;
        if (ignore === void 0) { ignore = false; }
        if (!this.isDataLoaded()) {
            (0, helpers_1.dhxWarning)("the method doesn't work with lazyLoad");
            return;
        }
        if (config === null || config === void 0 ? void 0 : config.smartSorting) {
            this._sorter = rule;
        }
        if (!ignore &&
            (!this._sortingStates.length ||
                (config === null || config === void 0 ? void 0 : config.smartSorting) ||
                (!((_a = this._sortingStates[0]) === null || _a === void 0 ? void 0 : _a.smartSorting) && !(config === null || config === void 0 ? void 0 : config.smartSorting)))) {
            this._sortingStates = [__assign(__assign({}, rule), config)];
        }
        if (rule) {
            if (!ignore) {
                this._initSortOrder = this._initSortOrder || __spreadArray([], (this._initFilterOrder || this._order), true);
                if (!(config === null || config === void 0 ? void 0 : config.smartSorting) && ((_b = this._sortingStates[0]) === null || _b === void 0 ? void 0 : _b.smartSorting)) {
                    var sortIndex = this._sortingStates.findIndex(function (i) { return i.by == rule.by; });
                    if (sortIndex !== -1) {
                        this._sortingStates[sortIndex].dir = rule.dir;
                        this._sortingStates.forEach(function (sort, index) {
                            _this.sort(sort, { smartSorting: !index }, true);
                        });
                    }
                    else {
                        this._sortingStates.push(rule);
                    }
                }
            }
            this._applySorters(rule);
        }
        else if (this._initSortOrder) {
            this._sortingStates = [];
            this._order = this._initSortOrder;
            this._sorter = this._initSortOrder = null;
            if (this._initFilterOrder) {
                this._initFilterOrder = null;
                this.filter(null, { $restore: true }, true);
            }
        }
        if (!ignore) {
            this.events.fire(types_1.DataEvents.change, [undefined, "sort", rule]);
        }
    };
    DataCollection.prototype.getSortingStates = function () {
        return this._sortingStates;
    };
    DataCollection.prototype.copy = function (id, index, target, targetId) {
        var _this = this;
        if (id instanceof Array) {
            return id.map(function (elementId, key) {
                return _this._copy(elementId, index, target, targetId, key);
            });
        }
        else {
            return this._copy(id, index, target, targetId);
        }
    };
    DataCollection.prototype.move = function (id, index, target, targetId, newId) {
        var _this = this;
        var _a;
        if (id instanceof Array) {
            var movedIds_1 = [];
            id.forEach(function (elementId, key) {
                if ((0, core_1.isId)(_this._move(elementId, index, target, targetId, key))) {
                    movedIds_1.push(elementId);
                }
                else {
                    (0, helpers_1.throwMoveWarning)(elementId, _this.exists(elementId));
                }
            });
            return movedIds_1;
        }
        else {
            return (_a = this._move(id, index, target, targetId, 0, newId)) !== null && _a !== void 0 ? _a : (0, helpers_1.throwMoveWarning)(id, this.exists(id));
        }
    };
    DataCollection.prototype.forEach = function (callback) {
        for (var i = 0; i < this._order.length; i++) {
            callback.call(this, this._order[i], i, this._order);
        }
    };
    DataCollection.prototype.load = function (url, driver) {
        if (typeof url === "string") {
            this.dataProxy = url = new dataproxy_1.DataProxy(url);
        }
        if (typeof driver === "string") {
            driver = driver.toLocaleLowerCase();
        }
        if (driver === "xml" ||
            driver === "csv" ||
            driver instanceof XMLDriver_1.XMLDriver ||
            driver instanceof CsvDriver_1.CsvDriver) {
            url.config.responseType = url.config.responseType || "text";
        }
        this.dataProxy = url;
        return this._loader.load(url, driver);
    };
    DataCollection.prototype.parse = function (data, driver) {
        return this._parse(data, driver);
    };
    DataCollection.prototype.$parse = function (data) {
        var apx = this.config.approximate;
        if (apx) {
            data = this._approximate(data, apx.value, apx.maxNum);
        }
        this._parse_data(data);
        this._reapplyFilters();
        this.events.fire(types_1.DataEvents.change, [undefined, "load"]);
        this.events.fire(types_1.DataEvents.load);
    };
    DataCollection.prototype.save = function (url) {
        if (typeof url === "string") {
            url = new dataproxy_1.DataProxy(url);
        }
        this._loader.save(url);
    };
    DataCollection.prototype.changeId = function (id, newId, silent) {
        if (newId === void 0) { newId = (0, core_1.uid)(); }
        if (id == newId)
            return;
        if (this.exists(newId)) {
            (0, helpers_1.dhxWarning)("item with ID ".concat(newId, " already exists"));
            return;
        }
        var item = this.getItem(id);
        if (!item) {
            (0, helpers_1.dhxWarning)("item not found");
        }
        else {
            item.id = newId;
            (0, core_1.extend)(this._pull[id], item);
            this._pull[newId] = this._pull[id];
            if (!silent) {
                this._onChange("update", newId, this._pull[newId]);
            }
            delete this._pull[id];
        }
    };
    // todo: loop through the array and check saved statuses
    DataCollection.prototype.isSaved = function () {
        return !this._changes.order.length; // todo: bad solution, errors and holded elments are missed...
    };
    DataCollection.prototype.map = function (callback) {
        var result = [];
        for (var i = 0; i < this._order.length; i++) {
            result.push(callback.call(this, this._order[i], i, this._order));
        }
        return result;
    };
    DataCollection.prototype.mapRange = function (from, to, callback) {
        if (from < 0)
            from = 0;
        if (to > this._order.length - 1)
            to = this._order.length - 1;
        var arr = this._order.slice(from, to + 1);
        var result = [];
        for (var i = from; i <= to; i++) {
            result.push(callback.call(this, this._order[i], i, arr));
        }
        return result;
    };
    DataCollection.prototype.reduce = function (callback, acc) {
        for (var i = 0; i < this._order.length; i++) {
            acc = callback.call(this, acc, this._order[i], i);
        }
        return acc;
    };
    DataCollection.prototype.serialize = function (driver) {
        if (driver === void 0) { driver = types_1.DataDriver.json; }
        // remove $ attrs
        var data = [];
        var _loop_1 = function (index) {
            var item = __assign({}, this_1._order[index]);
            Object.keys(item).forEach(function (key) {
                if (key.startsWith("$")) {
                    delete item[key];
                }
            });
            if (!(0, core_1.isDefined)(item.parent))
                delete item.parent;
            data.push(item);
        };
        var this_1 = this;
        for (var index = 0; index < this._order.length; index++) {
            _loop_1(index);
        }
        var dataDriver = (0, helpers_1.toDataDriver)(driver);
        if (dataDriver) {
            return dataDriver.serialize(data);
        }
    };
    DataCollection.prototype.getInitialData = function () {
        return this._initFilterOrder;
    };
    DataCollection.prototype.setMeta = function (obj, key, value) {
        if (!obj)
            return;
        var map = this._meta.get(obj);
        if (!map) {
            map = {};
            this._meta.set(obj, map);
        }
        map[key] = value;
    };
    DataCollection.prototype.getMeta = function (obj, key) {
        var map = this._meta.get(obj);
        return map ? map[key] : null;
    };
    DataCollection.prototype.getMetaMap = function (obj) {
        return this._meta.get(obj);
    };
    DataCollection.prototype.setRange = function (from, to) {
        this._range = !to ? null : [from, to];
    };
    DataCollection.prototype.getRawData = function (from, to, order, mode) {
        order = order || this._order;
        if (mode === 1)
            return order;
        if (this._range) {
            from = this._range[0] + from;
            if (to === -1) {
                to = this._range[1];
            }
            else {
                var diff = Math.abs(to - from);
                to = from + diff > this._range[1] ? this._range[1] : from + diff;
            }
        }
        if (!to || (from === 0 && (to === -1 || to === order.length))) {
            return order;
        }
        if (from >= order.length)
            return [];
        if (to === -1 || to > order.length)
            to = order.length;
        var slice = order.slice(from, to);
        if (slice.filter(function (item) { return item.$empty; }).length !== 0) {
            this.events.fire(types_1.DataEvents.dataRequest, [from, to]);
        }
        return slice;
    };
    DataCollection.prototype._add = function (newItem, index) {
        var id = this._addCore(newItem, index);
        this._onChange("add", newItem.id, newItem);
        this.events.fire(types_1.DataEvents.afterAdd, [newItem]);
        return id;
    };
    DataCollection.prototype._remove = function (id) {
        var removedItem = this._pull[id];
        if (removedItem) {
            if (!this.events.fire(types_1.DataEvents.beforeRemove, [removedItem])) {
                return;
            }
            this._removeCore(removedItem.id);
            this._onChange("remove", id, removedItem);
        }
        this.events.fire(types_1.DataEvents.afterRemove, [removedItem]);
    };
    DataCollection.prototype._copy = function (id, index, target, targetId, key) {
        if (!this.exists(id)) {
            return null;
        }
        var newid = (0, core_1.uid)();
        if (key) {
            index = index === -1 ? -1 : index + key;
        }
        if (target) {
            if (!(target instanceof DataCollection) && targetId) {
                target.add((0, helpers_1.copyWithoutInner)(this.getItem(id)), index);
                return;
            }
            if (target.exists(id)) {
                target.add(__assign(__assign({}, (0, helpers_1.copyWithoutInner)(this.getItem(id))), { id: newid }), index);
                return newid;
            }
            else {
                target.add((0, helpers_1.copyWithoutInner)(this.getItem(id)), index);
                return id;
            }
        }
        this.add(__assign(__assign({}, (0, helpers_1.copyWithoutInner)(this.getItem(id))), { id: newid }), index);
        return newid;
    };
    DataCollection.prototype._move = function (id, index, target, targetId, key, newId) {
        if (!this.exists(id)) {
            return null;
        }
        if (key && index < this.getIndex(id)) {
            index = index === -1 ? -1 : index + key;
        }
        if (target && target !== this && this.exists(id)) {
            var item = (0, core_1.copy)(this.getItem(id), true);
            if (newId)
                item.id = newId;
            if ((!newId && target.exists(id)) || target.exists(newId)) {
                item.id = (0, core_1.uid)();
            }
            if (targetId) {
                item.parent = targetId;
            }
            target.add(item, index);
            // remove data from original collection
            this.remove(id);
            return item.id;
        }
        if (this.getIndex(id) === index) {
            return null;
        }
        // move other elements
        var spliced = this._order.splice(this.getIndex(id), 1)[0];
        if (index === -1) {
            index = this._order.length;
        }
        this._order.splice(index, 0, spliced);
        this.events.fire(types_1.DataEvents.change, [id, "update", this.getItem(id)]);
        return id;
    };
    DataCollection.prototype._addCore = function (obj, index) {
        var _a;
        if (this.config.init) {
            obj = this.config.init(obj);
        }
        obj.id = (_a = obj.id) !== null && _a !== void 0 ? _a : (0, core_1.uid)();
        if (this._pull[obj.id]) {
            (0, helpers_1.dhxError)("Item ".concat(obj.id, " already exist"));
        }
        // todo: not ideal solution
        if (this._initFilterOrder && !(0, helpers_1.isTreeCollection)(this)) {
            this._addToOrder(this._initFilterOrder, obj, index);
        }
        if (this._initSortOrder) {
            this._addToOrder(this._initSortOrder, obj, index);
        }
        this._addToOrder(this._order, obj, index);
        return obj.id;
    };
    DataCollection.prototype._removeCore = function (id) {
        if (this._pull[id]) {
            this._order = this._order.filter(function (el) { return el.id !== id; });
            if (this._initFilterOrder && this._initFilterOrder.length) {
                this._initFilterOrder = this._initFilterOrder.filter(function (el) { return el.id !== id; });
            }
            if (this._initSortOrder && this._initSortOrder.length) {
                this._initSortOrder = this._initSortOrder.filter(function (el) { return el.id !== id; });
            }
            delete this._pull[id];
        }
    };
    DataCollection.prototype._parse_data = function (data) {
        var index = this._order.length;
        for (var _i = 0, data_1 = data; _i < data_1.length; _i++) {
            var obj = data_1[_i];
            this._addCore(obj, index++);
        }
    };
    DataCollection.prototype._approximate = function (data, values, maxNum) {
        var len = data.length;
        var vlen = values.length;
        var rlen = Math.floor(len / maxNum);
        var newData = Array(Math.ceil(len / rlen));
        var index = 0;
        for (var i = 0; i < len; i += rlen) {
            var newItem = (0, core_1.copy)(data[i]);
            var end = Math.min(len, i + rlen);
            for (var j = 0; j < vlen; j++) {
                var sum = 0;
                for (var z = i; z < end; z++) {
                    sum += data[z][values[j]];
                }
                newItem[values[j]] = sum / (end - i);
            }
            newData[index++] = newItem;
        }
        return newData;
    };
    DataCollection.prototype._onChange = function (status, id, obj) {
        var itemCount = 0;
        var maxStack = 10;
        for (var _i = 0, _a = this._changes.order; _i < _a.length; _i++) {
            var item = _a[_i];
            // update pending item if previous state is "saving" or if item not saved yet
            var index = this._changes.order.indexOf(item);
            if (item.id === id && !item.saving) {
                itemCount += 1;
                if (index === this._changes.order.length - 1 || this._changes.order[index + 1].id !== id) {
                    // update item
                    if (item.error) {
                        item.error = false;
                    }
                    item = __assign(__assign({}, item), { obj: obj, status: status });
                    itemCount += 1;
                    if (itemCount > maxStack) {
                        this._changes.order.splice(index, itemCount - maxStack, item);
                    }
                    else {
                        this._changes.order.splice(index + 1, 0, item);
                    }
                    this._loader.updateChanges(this._changes);
                    if (status === "remove" && obj.$emptyRow)
                        return;
                    this.events.fire(types_1.DataEvents.change, [id, status, obj]);
                    return;
                }
            }
        }
        this._changes.order.push({ id: id, status: status, obj: __assign({}, obj), saving: false });
        this._loader.updateChanges(this._changes);
        this.events.fire(types_1.DataEvents.change, [id, status, obj]);
    };
    DataCollection.prototype._addToOrder = function (array, obj, index) {
        if (index >= 0 && array[index]) {
            this._pull[obj.id] = obj;
            array.splice(index, 0, obj);
        }
        else {
            this._pull[obj.id] = obj;
            array.push(obj);
        }
    };
    DataCollection.prototype._applySorters = function (by) {
        this._sort.sort(this._order, by, this._sorter);
        // sort the not-filtered dataset
        if (this._initFilterOrder && this._initFilterOrder.length) {
            this._sort.sort(this._initFilterOrder, by, this._sorter);
        }
    };
    DataCollection.prototype._applyFilters = function (rule) {
        if (!rule)
            return;
        if (!this._checkFilterRule(rule)) {
            throw new Error("Invalid filter rule");
        }
        var filter = typeof rule !== "function" ? this._getRuleCallback(rule) : rule;
        var fOrder = this._order.filter(function (item) { return filter(item); });
        if (!this._initFilterOrder) {
            this._initFilterOrder = this._order;
        }
        this._order = fOrder;
    };
    DataCollection.prototype._reapplyFilters = function () {
        var permFilters = this.getFilters({ permanent: true });
        if (permFilters) {
            this.filter(permFilters, { $restore: true, add: true }, true);
        }
        if (this._sorter) {
            this._applySorters();
        }
    };
    DataCollection.prototype._getRuleCallback = function (rule) {
        if (!(0, core_1.isDefined)(rule.by) || !(0, core_1.isDefined)(rule.match))
            return;
        return rule.compare
            ? function (obj) { return rule.compare(obj[rule.by], rule.match, obj, rule.multi); }
            : function (obj) { return obj[rule.by] == rule.match; };
    };
    DataCollection.prototype._getPureFilters = function (filters) {
        return Object.keys(filters).reduce(function (obj, key) {
            var _a;
            if (!((_a = filters[key].config) === null || _a === void 0 ? void 0 : _a.$local)) {
                obj[key] = filters[key];
            }
            return obj;
        }, {});
    };
    DataCollection.prototype._normalizeFilters = function (filters) {
        var rules = [];
        for (var key in filters) {
            var rule = filters[key].rule;
            if (typeof rule !== "function") {
                if ((0, core_1.isDefined)(rule.by)) {
                    rules.push(this._getRuleCallback(rule));
                }
                else {
                    for (var key_1 in rule) {
                        rules.push(this._getRuleCallback(rule[key_1]));
                    }
                }
            }
            else {
                rules.push(rule);
            }
        }
        return __assign({}, rules);
    };
    DataCollection.prototype._checkFilterRule = function (rule) {
        return typeof rule === "function" || ((0, core_1.isDefined)(rule.by) && (0, core_1.isDefined)(rule.match));
    };
    DataCollection.prototype._parse = function (data, driver, grouping) {
        if (driver === void 0) { driver = types_1.DataDriver.json; }
        if (grouping === void 0) { grouping = false; }
        this._reset({ grouping: grouping });
        return this._loader.parse(data, driver);
    };
    return DataCollection;
}());
exports.DataCollection = DataCollection;


/***/ }),
/* 22 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.dataDriversPro = exports.dataDrivers = void 0;
var JsonDriver_1 = __webpack_require__(23);
var CsvDriver_1 = __webpack_require__(15);
var XMLDriver_1 = __webpack_require__(24);
exports.dataDrivers = {
    json: JsonDriver_1.JsonDriver,
    csv: CsvDriver_1.CsvDriver,
};
exports.dataDriversPro = __assign(__assign({}, exports.dataDrivers), { xml: XMLDriver_1.XMLDriver });


/***/ }),
/* 23 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.JsonDriver = void 0;
var JsonDriver = /** @class */ (function () {
    function JsonDriver() {
    }
    JsonDriver.prototype.toJsonArray = function (data) {
        return this.getRows(data);
    };
    JsonDriver.prototype.serialize = function (data) {
        return data;
    };
    JsonDriver.prototype.getFields = function (row) {
        return row;
    };
    JsonDriver.prototype.getRows = function (data) {
        return typeof data === "string" ? JSON.parse(data) : data;
    };
    return JsonDriver;
}());
exports.JsonDriver = JsonDriver;


/***/ }),
/* 24 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.XMLDriver = void 0;
var xml_1 = __webpack_require__(48);
var ARRAY_NAME = "items";
var ITEM_NAME = "item";
// convert xml tag to js object, all subtags and attributes are mapped to the properties of result object
function tagToObject(tag, initialObj) {
    initialObj = initialObj || {};
    // map attributes
    var a = tag.attributes;
    if (a && a.length) {
        for (var i = 0; i < a.length; i++) {
            initialObj[a[i].name] = a[i].value;
        }
    }
    // map subtags
    var b = tag.childNodes;
    for (var i = 0; i < b.length; i++) {
        var node = b[i];
        if (node.nodeType === node.ELEMENT_NODE) {
            var name_1 = node.tagName;
            if (initialObj[name_1]) {
                if (typeof initialObj[name_1].push !== "function") {
                    initialObj[name_1] = [initialObj[name_1]];
                }
                initialObj[name_1].push(tagToObject(node, {}));
            }
            else {
                initialObj[name_1] = tagToObject(node, {}); // sub-object for complex subtags
            }
        }
    }
    return initialObj;
}
var XMLDriver = /** @class */ (function () {
    function XMLDriver() {
    }
    XMLDriver.prototype.toJsonArray = function (data) {
        return this.getRows(data);
    };
    XMLDriver.prototype.toJsonObject = function (data) {
        var doc;
        if (typeof data === "string") {
            doc = this._fromString(data);
        }
        return tagToObject(doc);
    };
    XMLDriver.prototype.serialize = function (data) {
        return (0, xml_1.jsonToXML)(data);
    };
    XMLDriver.prototype.getFields = function (row) {
        return row;
    };
    XMLDriver.prototype.getRows = function (data) {
        if (typeof data === "string") {
            data = this._fromString(data);
        }
        if (data) {
            var childNodes = data.childNodes && data.childNodes[0] && data.childNodes[0].childNodes;
            if (!childNodes || !childNodes.length) {
                return null;
            }
            return this._getRows(childNodes);
        }
        return [];
    };
    XMLDriver.prototype._getRows = function (nodes) {
        var result = [];
        for (var i = 0; i < nodes.length; i++) {
            if (nodes[i].tagName === ITEM_NAME) {
                result.push(this._nodeToJS(nodes[i]));
            }
        }
        return result;
    };
    XMLDriver.prototype._fromString = function (data) {
        try {
            return new DOMParser().parseFromString(data, "text/xml");
        }
        catch (_a) {
            return null;
        }
    };
    XMLDriver.prototype._nodeToJS = function (node) {
        var result = {};
        if (this._haveAttrs(node)) {
            var attrs = node.attributes;
            for (var i = 0; i < attrs.length; i++) {
                var _a = attrs[i], name_2 = _a.name, value = _a.value;
                result[name_2] = this._toType(value);
            }
        }
        if (node.nodeType === node.TEXT_NODE) {
            result.value = result.value || this._toType(node.textContent);
            return result;
        }
        var childNodes = node.childNodes;
        if (childNodes) {
            for (var i = 0; i < childNodes.length; i++) {
                var subNode = childNodes[i];
                var tag = subNode.tagName;
                if (!tag) {
                    continue;
                }
                if (tag === ARRAY_NAME && subNode.childNodes) {
                    result[tag] = this._getRows(subNode.childNodes);
                }
                else {
                    if (this._haveAttrs(subNode)) {
                        result[tag] = this._nodeToJS(subNode);
                    }
                    else {
                        result[tag] = this._toType(subNode.textContent);
                    }
                }
            }
        }
        return result;
    };
    XMLDriver.prototype._toType = function (val) {
        if (val === "false" || val === "true") {
            return val === "true";
        }
        return val;
    };
    XMLDriver.prototype._haveAttrs = function (node) {
        return node.attributes && node.attributes.length;
    };
    return XMLDriver;
}());
exports.XMLDriver = XMLDriver;


/***/ }),
/* 25 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.methods = void 0;
var core_1 = __webpack_require__(0);
exports.methods = {
    sum: function (items, field) {
        return parseFloat(items.reduce(function (sum, item) { return sum + (+item[field] || 0); }, 0).toFixed(12));
    },
    avg: function (items, field) {
        return parseFloat((items.reduce(function (sum, item) { return sum + (+item[field] || 0); }, 0) / items.length).toFixed(12));
    },
    count: function (items, field) {
        if (items === void 0) { items = []; }
        return items.filter(function (item) { return (0, core_1.isDefined)(item[field]) && item[field] !== ""; }).length;
    },
    min: function (items, field) {
        return Math.min.apply(Math, items.map(function (item) { return +item[field]; }));
    },
    max: function (items, field) {
        return Math.max.apply(Math, items.map(function (item) { return +item[field]; }));
    },
};


/***/ }),
/* 26 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.rightGrid = exports.right = exports.leftGrid = exports.left = exports.topGrid = exports.top = exports.bottomGrid = exports.bottom = void 0;
var core_1 = __webpack_require__(0);
var dom_1 = __webpack_require__(1);
var common_1 = __webpack_require__(2);
var defaultTextTemplate = function (item) { return item.toString(); };
var getScaleAriaAttrs = function (axis, text) { return ({
    role: "graphics-object",
    "aria-label": "".concat(axis, "-axis").concat(text ? ", " + text : ""),
}); };
function bottom(points, config, width, height) {
    var title = config.title, textPadding = config.textPadding, scalePadding = config.scalePadding, textTemplate = config.textTemplate, showText = config.showText, scaleRotate = config.scaleRotate;
    var template = textTemplate || defaultTextTemplate;
    var text = [];
    var extraTittlePadding = 0;
    if (showText) {
        extraTittlePadding = textPadding;
        var canRotate_1 = scaleRotate && !isNaN(scaleRotate);
        var y_1 = height + textPadding;
        text = points.map(function (p) {
            var x = p[0];
            var transform = canRotate_1 ? "rotate(".concat(scaleRotate, " ").concat(x, " ").concat(y_1, ")") : "";
            var classList = ["scale-text", "top-text"];
            if (canRotate_1) {
                var angle = scaleRotate % 360;
                classList.push((0, common_1.getClassesForRotateScale)("bottom", angle));
            }
            return (0, dom_1.sv)("text", { x: x, y: y_1, class: classList.join(" "), transform: transform }, [
                (0, common_1.verticalCenteredText)(template(p[1])),
            ]);
        });
    }
    var id = (0, core_1.uid)();
    var svTitle = null;
    var mainLine = (0, dom_1.sv)("path", {
        class: "main-scale",
        d: "M0 ".concat(height, " H").concat(width - 0.5),
        id: id,
    });
    if (title) {
        svTitle = (0, dom_1.sv)("text", { dx: width / 2, dy: scalePadding + extraTittlePadding }, [
            (0, dom_1.sv)("textPath", { href: "#".concat(id), class: "scale-title " }, title),
        ]);
    }
    return (0, dom_1.sv)("g", __assign({}, getScaleAriaAttrs("x", title || config.text)), [mainLine, svTitle].concat(text));
}
exports.bottom = bottom;
function bottomGrid(points, width, height, config) {
    var dashed = config.dashed, grid = config.grid, targetLine = config.targetLine, targetValue = config.targetValue;
    var len = points.length;
    var gridLines = [];
    var className = "grid-line ".concat(dashed ? "dash-line" : "", " ").concat(!grid ? "hidden-line" : "");
    for (var i = 0; i < len; i++) {
        if (i === 0 && points[i][0] === 0 && !config.hidden) {
            continue;
        }
        if (i === targetLine) {
            var d_1 = "M".concat(points[i][0], " 0 V ").concat(height);
            var path_1 = (0, dom_1.sv)("path", { d: d_1, class: "".concat(className, " spec-grid-line") });
            gridLines.push(path_1);
            continue;
        }
        var d = "M".concat(points[i][0], " 0 V ").concat(height);
        var path = (0, dom_1.sv)("path", { d: d, class: className, _ref: "line" + Math.round(points[i][0]) });
        gridLines.push(path);
        if (i === len - 1 && points[i][0] !== width) {
            var additionD = "M".concat(width, " 0 V ").concat(height);
            var additionPath = (0, dom_1.sv)("path", { d: additionD, class: className });
            gridLines.push(additionPath);
        }
    }
    if (targetValue) {
        var d = "M".concat(targetValue * width, " 0 V ").concat(height);
        var path = (0, dom_1.sv)("path", { d: d, class: "".concat(className, " spec-grid-line") });
        gridLines.push(path);
    }
    return (0, dom_1.sv)("g", gridLines);
}
exports.bottomGrid = bottomGrid;
function top(points, config, width, _height) {
    var title = config.title, textPadding = config.textPadding, scalePadding = config.scalePadding, textTemplate = config.textTemplate, showText = config.showText, scaleRotate = config.scaleRotate;
    var template = textTemplate || defaultTextTemplate;
    var text = [];
    var extraTittlePadding = 0;
    if (showText) {
        extraTittlePadding = textPadding;
        var canRotate_2 = scaleRotate && !isNaN(scaleRotate);
        var y_2 = -textPadding;
        text = points.map(function (p) {
            var classList = ["scale-text"];
            var x = p[0];
            var transform = canRotate_2 ? "rotate(".concat(scaleRotate, " ").concat(x, " ").concat(y_2, ")") : "";
            if (canRotate_2) {
                var angle = scaleRotate % 360;
                classList.push((0, common_1.getClassesForRotateScale)("top", angle));
            }
            return (0, dom_1.sv)("text", { x: x, y: y_2, class: classList.join(" "), transform: transform }, [
                (0, common_1.verticalCenteredText)(template(p[1])),
            ]);
        });
    }
    var id = (0, core_1.uid)();
    var mainLine = (0, dom_1.sv)("path", { d: "M0 0 H".concat(width), class: "main-scale", id: id });
    var svTitle = null;
    if (title) {
        svTitle = (0, dom_1.sv)("text", { dx: width / 2, dy: -scalePadding - extraTittlePadding }, [
            (0, dom_1.sv)("textPath", { href: "#".concat(id), class: "scale-title" }, title),
        ]);
    }
    return (0, dom_1.sv)("g", __assign({}, getScaleAriaAttrs("x", title || config.text)), [mainLine, svTitle].concat(text));
}
exports.top = top;
function topGrid(points, _width, height, config) {
    var dashed = config.dashed, grid = config.grid, targetLine = config.targetLine;
    var len = points.length;
    var gridLines = [];
    var className = "grid-line ".concat(dashed ? "dash-line" : "", " ").concat(!grid ? "hidden-line" : "");
    for (var i = 0; i < len; i++) {
        if (i === 0 && points[i][0] === 0 && !config.hidden) {
            continue;
        }
        if (i === targetLine) {
            var d_2 = "M".concat(points[i][0], " 0 V ").concat(height);
            var path_2 = (0, dom_1.sv)("path", { d: d_2, class: "".concat(className, " spec-grid-line") });
            gridLines.push(path_2);
            continue;
        }
        var d = "M".concat(points[i][0], " 0 V ").concat(height);
        var path = (0, dom_1.sv)("path", { d: d, class: className, _ref: "line" + Math.round(points[i][0]) });
        gridLines.push(path);
        if (i === len - 1 && points[i][0] !== 0) {
            var additionD = "M0 0 V ".concat(height);
            var additionPath = (0, dom_1.sv)("path", { d: additionD, class: className });
            gridLines.push(additionPath);
        }
    }
    return (0, dom_1.sv)("g", gridLines);
}
exports.topGrid = topGrid;
function left(points, config, _width, height) {
    var title = config.title, textPadding = config.textPadding, scalePadding = config.scalePadding, textTemplate = config.textTemplate, showText = config.showText, scaleRotate = config.scaleRotate;
    var template = textTemplate || defaultTextTemplate;
    var text = [];
    var extraTittlePadding = 0;
    if (showText) {
        var style_1 = (0, common_1.getFontStyle)("scale-text");
        var maxTextWidth_1 = 0;
        var canRotate_3 = scaleRotate && !isNaN(scaleRotate);
        text = points.map(function (p) {
            var y = p[0];
            var x = -textPadding;
            var transform = canRotate_3 ? "rotate(".concat(scaleRotate, " ").concat(x, " ").concat(y, ")") : "";
            var classList = ["scale-text"];
            var scaleText = template(p[1]);
            if (title) {
                var textWidth = (0, common_1.getTextWidth)(scaleText, style_1);
                if (maxTextWidth_1 < textWidth) {
                    maxTextWidth_1 = textWidth;
                }
            }
            if (canRotate_3) {
                var angle = scaleRotate % 360;
                classList.push((0, common_1.getClassesForRotateScale)("left", angle));
            }
            else {
                classList.push("end-text");
            }
            return (0, dom_1.sv)("text", { x: x, y: y, class: classList.join(" "), transform: transform }, [
                (0, common_1.verticalCenteredText)(scaleText),
            ]);
        });
        extraTittlePadding = maxTextWidth_1 + textPadding;
    }
    var id = (0, core_1.uid)();
    var mainLine = (0, dom_1.sv)("path", {
        class: "main-scale",
        d: "M0 ".concat(height, " V 0.5"),
        id: id,
        _ref: points.length ? "line0" : null,
    }); // 0.5 instead of 0, coz stroke-linecap: square and dirrent stroke size
    var svTitle = null;
    if (title) {
        svTitle = (0, dom_1.sv)("text", { dx: height / 2, dy: -scalePadding - extraTittlePadding }, [
            (0, dom_1.sv)("textPath", { href: "#".concat(id), class: "scale-title" }, title),
        ]);
    }
    return (0, dom_1.sv)("g", __assign({}, getScaleAriaAttrs("y", title || config.text)), [mainLine, svTitle].concat(text));
}
exports.left = left;
function leftGrid(points, width, height, config) {
    var dashed = config.dashed, grid = config.grid, targetLine = config.targetLine, targetValue = config.targetValue;
    var len = points.length;
    var gridLines = [];
    var className = "grid-line ".concat(dashed ? "dash-line" : "");
    for (var i = 0; i < len; i++) {
        if (i === 0 && points[i][0] === height && !config.hidden) {
            continue;
        }
        if (targetLine === i) {
            var d = "M0 ".concat(points[i][0], " H ").concat(width);
            var path = (0, dom_1.sv)("path", { d: d, class: "".concat(className, " spec-grid-line") });
            gridLines.push(path);
            continue;
        }
        if (grid) {
            var d = "M0 ".concat(points[i][0], " H ").concat(width);
            var path = (0, dom_1.sv)("path", { d: d, class: className });
            gridLines.push(path);
            if (i === len - 1 && points[i][0] !== width) {
                var additionD = "M0 0 H".concat(width);
                var additionPath = (0, dom_1.sv)("path", { d: additionD, class: className });
                gridLines.push(additionPath);
            }
        }
    }
    if (targetValue) {
        var d = "M0 ".concat(targetValue * height, " H ").concat(width);
        var path = (0, dom_1.sv)("path", { d: d, class: "".concat(className, " spec-grid-line") });
        gridLines.push(path);
    }
    return (0, dom_1.sv)("g", gridLines);
}
exports.leftGrid = leftGrid;
function right(points, config, width, height) {
    var title = config.title, textPadding = config.textPadding, scalePadding = config.scalePadding, textTemplate = config.textTemplate, showText = config.showText, scaleRotate = config.scaleRotate;
    var template = textTemplate || defaultTextTemplate;
    var text = [];
    var extraTittlePadding = 0;
    if (showText) {
        var style_2 = (0, common_1.getFontStyle)("scale-text");
        var maxTextWidth_2 = 0;
        var canRotate_4 = scaleRotate && !isNaN(scaleRotate);
        text = points.map(function (p) {
            var scaleText = template(p[1]);
            var y = p[0];
            var x = width + textPadding;
            var transform = canRotate_4 ? "rotate(".concat(scaleRotate, " ").concat(x, " ").concat(y, ")") : "";
            var classList = ["scale-text"];
            if (title) {
                var textWidth = (0, common_1.getTextWidth)(scaleText, style_2);
                if (maxTextWidth_2 < textWidth) {
                    maxTextWidth_2 = textWidth;
                }
            }
            if (canRotate_4) {
                var angle = scaleRotate % 360;
                classList.push((0, common_1.getClassesForRotateScale)("right", angle));
            }
            else {
                classList.push("start-text");
            }
            return (0, dom_1.sv)("text", { x: x, y: y, class: classList.join(" "), transform: transform }, [
                (0, common_1.verticalCenteredText)(scaleText),
            ]);
        });
        extraTittlePadding = textPadding + maxTextWidth_2;
    }
    var id = (0, core_1.uid)();
    var mainLine = (0, dom_1.sv)("path", {
        d: "M".concat(width, " ").concat(height, " V 0"),
        class: "main-scale",
        id: id,
        _ref: points.length ? "line0" : null,
    });
    var svTitle = null;
    if (title) {
        svTitle = (0, dom_1.sv)("text", { dx: height / 2, dy: scalePadding + extraTittlePadding }, [
            (0, dom_1.sv)("textPath", { href: "#".concat(id), class: "scale-title" }, title),
        ]);
    }
    return (0, dom_1.sv)("g", __assign({}, getScaleAriaAttrs("y", title || config.text)), [mainLine, svTitle].concat(text));
}
exports.right = right;
function rightGrid(points, width, height, config) {
    var dashed = config.dashed, grid = config.grid, targetLine = config.targetLine;
    var len = points.length;
    var gridLines = [];
    var className = "grid-line ".concat(dashed ? "dash-line" : "");
    for (var i = 0; i < len; i++) {
        if (i === 0 && points[i][0] === height && !config.hidden) {
            continue;
        }
        if (targetLine === i) {
            var d = "M0 ".concat(points[i][0], " H ").concat(width);
            var path = (0, dom_1.sv)("path", { d: d, class: "".concat(className, " spec-grid-line") });
            gridLines.push(path);
            continue;
        }
        if (grid) {
            var d = "M0 ".concat(points[i][0], " H ").concat(width);
            var path = (0, dom_1.sv)("path", { d: d, class: className });
            gridLines.push(path);
            if (i === len - 1 && points[i][0] !== width) {
                var additionD = "M0 0 H".concat(width);
                var additionPath = (0, dom_1.sv)("path", { d: additionD, class: className });
                gridLines.push(additionPath);
            }
        }
    }
    return (0, dom_1.sv)("g", gridLines);
}
exports.rightGrid = rightGrid;


/***/ }),
/* 27 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
var dom_1 = __webpack_require__(1);
var common_1 = __webpack_require__(2);
var ScaleSeria_1 = __webpack_require__(17);
var Area = /** @class */ (function (_super) {
    __extends(Area, _super);
    function Area() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    Area.prototype.paint = function (width, height, prev) {
        _super.prototype.paint.call(this, width, height);
        var getChartAriaAttrs = function (cfg) { return ({
            "aria-label": "chart ".concat(cfg.value || ""),
        }); };
        var svg = [];
        this._form(width, height, svg, prev);
        this._markers(svg);
        return (0, dom_1.sv)("g", __assign(__assign({ class: "seria", _key: this.id }, getChartAriaAttrs(this.config)), { tabindex: 0 }), svg);
    };
    Area.prototype.paintformAndMarkers = function (width, height, prev) {
        _super.prototype.paint.call(this, width, height);
        var svg = [];
        var markers = [];
        this._form(width, height, svg, prev);
        this._markers(markers);
        return [
            (0, dom_1.sv)("g", { class: "seria", _key: this.id }, svg),
            (0, dom_1.sv)("g", { class: "seria_markers", _key: this.id + "_markers" }, markers),
        ];
    };
    Area.prototype._markers = function (svg) {
        var _this = this;
        if (this.config.pointType) {
            var color = this.config.pointColor || this.config.color;
            var point_1 = this._getPointType(this.config.pointType, color);
            svg.push.apply(svg, this._points.map(function (p) { return point_1(p[0], p[1], (0, common_1.calcPointRef)(p[2], _this.id)); }));
        }
    };
    Area.prototype._form = function (width, height, svg, prev) {
        var _a = this.config, id = _a.id, fill = _a.fill, alpha = _a.alpha, color = _a.color, strokeWidth = _a.strokeWidth;
        var points = this._points;
        var last = points[points.length - 1];
        var d = "";
        if (prev) {
            // bottom line in stacked area
            for (var i = prev.length - 1; i >= 0; i--) {
                var item = prev[i];
                d += i === points.length - 1 ? "M".concat(item[0], " ").concat(item[1], " ") : "L".concat(item[0], " ").concat(item[1], " ");
            }
            // top line in stacked area
            d +=
                points.map(function (item, index) { return (!index ? "V ".concat(item[1]) : "L ".concat(item[0], " ").concat(item[1])); }).join(" ") +
                    "Z";
        }
        else {
            d +=
                points
                    .map(function (item, index) {
                    return index ? "L".concat(item[0], " ").concat(item[1]) : "M0 ".concat(height, " L0 ").concat(item[1], " L").concat(item[0], " ").concat(item[1]);
                })
                    .join(" ") + "L".concat(width, " ").concat(last[1], " V ").concat(height);
        }
        if (strokeWidth) {
            var len_1 = points.length - 1;
            var strokePadding_1 = function (index) { return (index === len_1 ? -0.5 : index ? 0 : 0.5); };
            var line = points
                .map(function (item, index) {
                return index
                    ? "L".concat(item[0] + strokePadding_1(index), " ").concat(item[1])
                    : "M0 ".concat(item[1], " L0 ").concat(item[1] + strokePadding_1(index), " L").concat(item[0] +
                        strokePadding_1(index), " ").concat(item[1]);
            })
                .join(" ") + "L".concat(width, " ").concat(last[1]);
            var linePath = (0, dom_1.sv)("path", {
                d: line,
                "stroke-width": strokeWidth,
                stroke: color,
                fill: "none",
                class: this._getCss(),
            });
            svg.push(linePath);
        }
        var path = (0, dom_1.sv)("path", {
            id: "seria" + id,
            d: d,
            class: this._getCss(),
            fill: fill,
            _ref: id,
            "fill-opacity": alpha,
            stroke: "none",
        });
        svg.push(path);
        return svg;
    };
    Area.prototype._setDefaults = function (config) {
        var defaults = {
            alpha: 0.3,
            strokeWidth: 2,
            fill: config.color || "#5E83BA",
            color: "#5E83BA",
            active: true,
            tooltip: true,
            pointType: "empty",
        };
        this.config = __assign(__assign({}, defaults), config);
        var point = this.config.pointType;
        var color = this.config.pointColor || this.config.color;
        if (point) {
            this._drawPointType = this._getPointType(point, color);
        }
    };
    return Area;
}(ScaleSeria_1.default));
exports.default = Area;


/***/ }),
/* 28 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.getShadeHTMLHelper = exports.getShadeHelper = exports.getHTMLHelper = exports.getHelper = void 0;
var dom_1 = __webpack_require__(1);
var common_1 = __webpack_require__(2);
var forms = {
    circle: function (color, fill, _alpha, x, y, id) {
        var config = {
            _ref: id,
            cx: x,
            cy: y,
            r: 4,
            class: "figure point-circle",
            fill: fill,
            stroke: color,
            "stroke-width": 2,
        };
        return (0, dom_1.sv)("circle", config);
    },
    rect: function (color, fill, _alpha, x, y, id) {
        var config = {
            _ref: id,
            x: x - 4,
            y: y - 4,
            width: 8,
            height: 8,
            class: "figure point-rect",
            fill: fill,
            stroke: color,
            "stroke-width": 2,
        };
        return (0, dom_1.sv)("rect", config);
    },
    rhombus: function (color, fill, _alpha, x, y, id) {
        var config = {
            _ref: id,
            points: "".concat(x - 5, ",").concat(y, " ").concat(x, ",").concat(y + 5, " ").concat(x + 5, ",").concat(y, " ").concat(x, ",").concat(y - 5),
            class: "figure point-rhombus",
            fill: fill,
            stroke: color,
            "stroke-width": 2,
        };
        return (0, dom_1.sv)("polygon", config);
    },
    triangle: function (color, fill, _alpha, x, y, id) {
        var config = {
            _ref: id,
            points: "".concat(x, ",").concat(y - 5, " ").concat(x + 5, ",").concat(y + 5, " ").concat(x - 5, ",").concat(y + 5),
            class: "figure point-triangle",
            fill: fill,
            stroke: color,
            "stroke-width": 2,
        };
        return (0, dom_1.sv)("polygon", config);
    },
    simpleCircle: function (color, _fill, _alpha, x, y, id) {
        var config = {
            _ref: id,
            cx: x,
            cy: y,
            r: 3,
            class: "figure point-simple-circle",
            fill: color,
        };
        return (0, dom_1.sv)("circle", config);
    },
    simpleRect: function (color, _fill, _alpha, x, y, id) {
        var config = {
            _ref: id,
            x: x - 3,
            y: y - 3,
            width: 6,
            height: 6,
            class: "figure point-simple-rect",
            fill: color,
        };
        return (0, dom_1.sv)("rect", config);
    },
    empty: function () {
        return null;
    },
};
var formsHTML = {
    circle: function (color, fill, _alpha, x, y, id) {
        return "<circle class=\"figure point-circle\" _ref=\"".concat(id, "\" cx=\"").concat(x, "\" cy=\"").concat(y, "\" r=\"4\" fill=\"").concat(fill, "\" stroke=\"").concat(color, "\" stroke-width=\"2\"/>");
    },
    rect: function (color, fill, _alpha, x, y, id) {
        return "<rect _ref=\"".concat(id, "\" x=\"").concat(x - 4, "\" y=\"").concat(y -
            4, "\" width=\"8\" height=\"8\" class=\"figure point-rect\" fill=\"").concat(fill, "\" stroke=\"").concat(color, "\" stroke-width=\"2\"/>");
    },
    rhombus: function (color, fill, _alpha, x, y, id) {
        return "<polygon _ref=\"".concat(id, "\" points=\"").concat(x - 5, ",").concat(y, " ").concat(x, ",").concat(y + 5, " ").concat(x + 5, ",").concat(y, " ").concat(x, ",").concat(y -
            5, "\" class=\"figure point-rhombus\" fill=\"").concat(fill, "\" stroke=\"").concat(color, "\" stroke-width=\"2\"/>");
    },
    triangle: function (color, fill, _alpha, x, y, id) {
        return "<polygon _ref=\"".concat(id, "\" points=\"").concat(x, ",").concat(y - 5, " ").concat(x + 5, ",").concat(y + 5, " ").concat(x - 5, ",").concat(y +
            5, "\" class=\"figure point-triangle\" fill=\"").concat(fill, "\" stroke=\"").concat(color, "\" stroke-width=\"2\"/>");
    },
    simpleCircle: function (color, _fill, _alpha, x, y, id) {
        return "<circle _ref=\"".concat(id, "\" cx=\"").concat(x, "\" cy=\"").concat(y, "\" r=\"3\" class=\"figure point-simple-circle\" fill=\"").concat(color, "\"/>");
    },
    simpleRect: function (color, _fill, _alpha, x, y, id) {
        return "<rect _ref=\"id\" x=\"".concat(x - 3, "\" y=\"").concat(y -
            3, "\" width=\"6\" height=\"6\" class=\"figure point-simple-rect\" fill=\"").concat(color, "\"/>");
    },
    empty: function () {
        return null;
    },
};
function getHelper(type) {
    var helper = forms[type.toString()];
    if (!helper) {
        throw new Error("unknown point type");
    }
    return helper;
}
exports.getHelper = getHelper;
function getHTMLHelper(type) {
    var helper = formsHTML[type.toString()];
    if (!helper) {
        throw new Error("unknown point type");
    }
    return helper;
}
exports.getHTMLHelper = getHTMLHelper;
function getShadeHelper(type, color) {
    var helper = getHelper(type);
    color = color || "none";
    var shade = (0, common_1.getColorShade)(color, 0.2);
    return function (x, y, id) {
        return helper(color, shade, "", x, y, id);
    };
}
exports.getShadeHelper = getShadeHelper;
function getShadeHTMLHelper(type, color) {
    var helper = getHTMLHelper(type);
    color = color || "none";
    var shade = (0, common_1.getColorShade)(color, 0.2);
    return function (x, y, id) {
        return helper(color, shade, "", x, y, id);
    };
}
exports.getShadeHTMLHelper = getShadeHTMLHelper;


/***/ }),
/* 29 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.DateHelper = exports.stringToDate = exports.getFormattedDate = exports.locale = void 0;
var core_1 = __webpack_require__(0);
var core_2 = __webpack_require__(0);
exports.locale = {
    monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
    months: [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
    ],
    daysShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
    days: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Monday"],
    cancel: "Cancel",
};
/*
    %d	day as a number with leading zero, 01..31
    %j	day as a number, 1..31
    %D	short name of the day, Su Mo Tu...
    %l	full name of the day, Sunday Monday Tuesday...
    %m	month as a number with leading zero, 01..12
    %n	month as a number, 1..12
    %M	short name of the month, Jan Feb Mar...
    %F	full name of the month, January February March...
    %y	year as a number, 2 digits
    %Y	year as a number, 4 digits
    %h	hours 12-format with leading zero, 01..12)
    %g	hours 12-format, 1..12)
    %H	hours 24-format with leading zero, 01..24
    %G	hours 24-format, 1..24
    %i	minutes with leading zero, 01..59
    %s	seconds with leading zero, 01..59
    %a	am or pm
    %A	AM or PM
    %u	milliseconds
*/
var formatters = {
    "%d": function (date) {
        var day = date.getDate();
        return day < 10 ? "0" + day : day;
    },
    "%j": function (date) { return date.getDate(); },
    "%l": function (date) {
        return exports.locale.days[date.getDay()];
    },
    "%D": function (date) {
        return exports.locale.daysShort[date.getDay()];
    },
    "%m": function (date) {
        var month = date.getMonth() + 1;
        return month < 10 ? "0" + month : month;
    },
    "%n": function (date) { return date.getMonth() + 1; },
    "%M": function (date) { return exports.locale.monthsShort[date.getMonth()]; },
    "%F": function (date) { return exports.locale.months[date.getMonth()]; },
    "%y": function (date) {
        return date
            .getFullYear()
            .toString()
            .slice(2);
    },
    "%Y": function (date) { return date.getFullYear(); },
    "%h": function (date) {
        var hours = date.getHours() % 12;
        if (hours === 0) {
            hours = 12;
        }
        return hours < 10 ? "0" + hours : hours;
    },
    "%g": function (date) {
        var hours = date.getHours() % 12;
        if (hours === 0) {
            hours = 12;
        }
        return hours;
    },
    "%H": function (date) {
        var hours = date.getHours();
        return hours < 10 ? "0" + hours : hours;
    },
    "%G": function (date) { return date.getHours(); },
    "%i": function (date) {
        var minutes = date.getMinutes();
        return minutes < 10 ? "0" + minutes : minutes;
    },
    "%s": function (date) {
        var seconds = date.getSeconds();
        return seconds < 10 ? "0" + seconds : seconds;
    },
    "%a": function (date) {
        return date.getHours() >= 12 ? "pm" : "am";
    },
    "%A": function (date) {
        return date.getHours() >= 12 ? "PM" : "AM";
    },
    "%u": function (date) { return date.getMilliseconds(); },
};
var setFormatters = {
    "%d": function (date, value, _format, validate) {
        var check = /(^([0-9][0-9])$)/i.test(value);
        if (validate) {
            return check;
        }
        check ? date.setDate(Number(value)) : date.setDate(Number(1));
    },
    "%j": function (date, value, _format, validate) {
        var check = /(^([0-9]?[0-9])$)/i.test(value);
        if (validate) {
            return check;
        }
        check ? date.setDate(Number(value)) : date.setDate(Number(1));
    },
    "%m": function (date, value, _format, validate) {
        var check = /(^([0-9][0-9])$)/i.test(value);
        if (validate) {
            return check;
        }
        check ? date.setMonth(Number(value) - 1) : date.setMonth(Number(0));
        if (check && date.getMonth() !== Number(value) - 1)
            date.setMonth(Number(value) - 1);
    },
    "%n": function (date, value, _format, validate) {
        var check = /(^([0-9]?[0-9])$)/i.test(value);
        if (validate) {
            return check;
        }
        check ? date.setMonth(Number(value) - 1) : date.setMonth(Number(0));
        if (check && date.getMonth() !== Number(value) - 1)
            date.setMonth(Number(value) - 1);
    },
    "%M": function (date, value, _format, validate) {
        var index = (0, core_2.findIndex)(exports.locale.monthsShort, function (v) { return v === value; });
        if (validate) {
            return index !== -1;
        }
        index === -1 ? date.setMonth(0) : date.setMonth(index);
        if (index !== -1 && date.getMonth() !== index)
            date.setMonth(index);
    },
    "%F": function (date, value, _format, validate) {
        var index = (0, core_2.findIndex)(exports.locale.months, function (v) { return v === value; });
        if (validate) {
            return index !== -1;
        }
        index === -1 ? date.setMonth(0) : date.setMonth(index);
        if (index !== -1 && date.getMonth() !== index)
            date.setMonth(index);
    },
    "%y": function (date, value, _format, validate) {
        var check = /(^([0-9][0-9])$)/i.test(value);
        if (validate) {
            return check;
        }
        check ? date.setFullYear(Number("20" + value)) : date.setFullYear(Number("2000"));
    },
    "%Y": function (date, value, _format, validate) {
        var check = /(^([0-9][0-9][0-9][0-9])$)/i.test(value);
        if (validate) {
            return check;
        }
        check ? date.setFullYear(Number(value)) : date.setFullYear(Number("2000"));
    },
    "%h": function (date, value, dateFormat, validate) {
        var check = /(^0[1-9]|1[0-2]$)/i.test(value);
        if (validate) {
            return check;
        }
        (check && (dateFormat === "am" || dateFormat === "pm")) || dateFormat === "AM" || dateFormat === "PM"
            ? date.setHours(Number(value))
            : date.setHours(Number(0));
    },
    "%g": function (date, value, dateFormat, validate) {
        var check = /(^[1-9]$)|(^0[1-9]|1[0-2]$)/i.test(value);
        if (validate) {
            return check;
        }
        (check && (dateFormat === "am" || dateFormat === "pm")) || dateFormat === "AM" || dateFormat === "PM"
            ? date.setHours(Number(value))
            : date.setHours(Number(0));
    },
    "%H": function (date, value, _format, validate) {
        var check = /(^[0-2][0-9]$)/i.test(value);
        if (validate) {
            return check;
        }
        check ? date.setHours(Number(value)) : date.setHours(Number(0));
    },
    "%G": function (date, value, _format, validate) {
        var check = /(^[1-9][0-9]?$)/i.test(value);
        if (validate) {
            return check;
        }
        check ? date.setHours(Number(value)) : date.setHours(Number(0));
    },
    "%i": function (date, value, _format, validate) {
        var check = /(^([0-5][0-9])$)/i.test(value);
        if (validate) {
            return check;
        }
        check ? date.setMinutes(Number(value)) : date.setMinutes(Number(0));
    },
    "%s": function (date, value, _format, validate) {
        var check = /(^([0-5][0-9])$)/i.test(value);
        if (validate) {
            return check;
        }
        check ? date.setSeconds(Number(value)) : date.setSeconds(Number(0));
    },
    "%u": function (date, value, _format, validate) {
        var check = /(^([0-9][0-9][0-9])$)/i.test(value);
        if (validate) {
            return check;
        }
        check ? date.setMilliseconds(Number(value)) : date.setMilliseconds(Number(0));
    },
    "%a": function (date, value, _format, validate) {
        if (validate) {
            return value === "pm" || value === "am";
        }
        value === "pm" && date.setHours(date.getHours() + 12);
    },
    "%A": function (date, value, _format, validate) {
        if (validate) {
            return value === "PM" || value === "AM";
        }
        value === "PM" && date.setHours(date.getHours() + 12);
    },
};
var TokenType;
(function (TokenType) {
    TokenType[TokenType["separator"] = 0] = "separator";
    TokenType[TokenType["datePart"] = 1] = "datePart";
})(TokenType || (TokenType = {}));
function tokenizeFormat(format) {
    var tokens = [];
    var currentSeparator = "";
    for (var i = 0; i < format.length; i++) {
        if (format[i] === "%") {
            if (currentSeparator.length > 0) {
                tokens.push({
                    type: TokenType.separator,
                    value: currentSeparator,
                });
                currentSeparator = "";
            }
            tokens.push({
                type: TokenType.datePart,
                value: format[i] + format[i + 1],
            });
            i++;
        }
        else {
            currentSeparator += format[i];
        }
    }
    if (currentSeparator.length > 0) {
        tokens.push({
            type: TokenType.separator,
            value: currentSeparator,
        });
    }
    return tokens;
}
function getFormattedDate(format, date) {
    return tokenizeFormat(format).reduce(function (res, token) {
        if (token.type === TokenType.separator) {
            return res + token.value;
        }
        else {
            if (!formatters[token.value]) {
                return res;
            }
            return res + formatters[token.value](date);
        }
    }, "");
}
exports.getFormattedDate = getFormattedDate;
var datePartQueue = { "%Y": 1, "%y": 1, "%M": 2, "%F": 2, "%m": 2, "%n": 2 };
function stringToDate(str, format, validate) {
    if (typeof str !== "string") {
        return;
    }
    format = format.replace(/([a-z])(%a)/i, function () {
        var match = [];
        for (var _i = 0; _i < arguments.length; _i++) {
            match[_i] = arguments[_i];
        }
        str = str.replace(/(am|pm)/i, " $&");
        return match[1] + " " + match[2];
    });
    var tokens = tokenizeFormat(format);
    var dateParts = new Array(2);
    var index = 0;
    var formatter = null;
    var dateFormat;
    var message = "Incorrect date, see docs: https://docs.dhtmlx.com/suite/calendar__api__calendar_dateformat_config.html";
    var addDatePart = function (part) {
        var queue = datePartQueue[part.formatter];
        if (queue) {
            dateParts[queue - 1] = part;
            return;
        }
        if (part.formatter === "%A" || part.formatter === "%a") {
            dateFormat = part.value;
        }
        dateParts.push(part);
    };
    for (var i = 0; i < tokens.length; i++) {
        if (tokens[i].type === TokenType.separator) {
            var separatorIndex = str.indexOf(tokens[i].value, index);
            if (separatorIndex === -1) {
                if (validate) {
                    return false;
                }
                throw new Error(message);
            }
            if (formatter) {
                addDatePart({
                    formatter: formatter,
                    value: str.slice(index, separatorIndex),
                });
                formatter = null;
            }
            index = separatorIndex + tokens[i].value.length;
        }
        else if (tokens[i].type === TokenType.datePart) {
            if (tokens[i + 1] && tokens[i + 1].type !== TokenType.separator) {
                if (validate) {
                    return false;
                }
                throw new Error(message);
            }
            else {
                formatter = tokens[i].value;
            }
        }
    }
    if (formatter) {
        addDatePart({
            formatter: formatter,
            value: str.slice(index),
        });
    }
    var date = new Date(0);
    for (var _i = 0, dateParts_1 = dateParts; _i < dateParts_1.length; _i++) {
        var datePart = dateParts_1[_i];
        if (!datePart)
            continue;
        if (setFormatters[datePart.formatter]) {
            if (validate && !setFormatters[datePart.formatter](date, datePart.value, dateFormat, validate)) {
                return false;
            }
            setFormatters[datePart.formatter](date, datePart.value, dateFormat);
        }
    }
    return validate ? true : date;
}
exports.stringToDate = stringToDate;
var DateHelper = exports.DateHelper = /** @class */ (function () {
    function DateHelper() {
    }
    DateHelper.copy = function (d) {
        return new Date(d);
    };
    DateHelper.fromYear = function (year) {
        return new Date(year, 0, 1);
    };
    DateHelper.fromYearAndMonth = function (year, month) {
        return new Date(year, month, 1);
    };
    DateHelper.weekStart = function (d, firstWeekday) {
        var diff = (d.getDay() + 7 - firstWeekday) % 7;
        return new Date(d.getFullYear(), d.getMonth(), d.getDate() - diff);
    };
    DateHelper.monthStart = function (d) {
        return new Date(d.getFullYear(), d.getMonth(), 1);
    };
    DateHelper.yearStart = function (d) {
        return new Date(d.getFullYear(), 0, 1);
    };
    DateHelper.dayStart = function (d) {
        return new Date(d.getFullYear(), d.getMonth(), d.getDate());
    };
    DateHelper.addDay = function (d, count) {
        if (count === void 0) { count = 1; }
        return new Date(d.getFullYear(), d.getMonth(), d.getDate() + count);
    };
    DateHelper.addMonth = function (d, count) {
        if (count === void 0) { count = 1; }
        return new Date(d.getFullYear(), d.getMonth() + count);
    };
    DateHelper.addYear = function (d, count) {
        if (count === void 0) { count = 1; }
        return new Date(d.getFullYear() + count, d.getMonth());
    };
    DateHelper.withHoursAndMinutes = function (d, hours, minutes, dateFormat) {
        if (dateFormat === undefined || (!dateFormat && hours === 12) || (dateFormat && hours !== 12)) {
            return new Date(d.getFullYear(), d.getMonth(), d.getDate(), hours, minutes);
        }
        else if (dateFormat && hours === 12) {
            return new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0, minutes);
        }
        else {
            return new Date(d.getFullYear(), d.getMonth(), d.getDate(), hours + 12, minutes);
        }
    };
    DateHelper.setMonth = function (d, month) {
        d.setMonth(month);
    };
    DateHelper.setYear = function (d, year) {
        d.setFullYear(year);
    };
    DateHelper.mergeHoursAndMinutes = function (source, target) {
        return new Date(source.getFullYear(), source.getMonth(), source.getDate(), target.getHours(), target.getMinutes());
    };
    DateHelper.isWeekEnd = function (d) {
        return d.getDay() === 0 || d.getDay() === 6;
    };
    DateHelper.getTwelweYears = function (d) {
        var y = d.getFullYear();
        var firstYear = y - (y % 12);
        return (0, core_1.range)(firstYear, firstYear + 11);
    };
    DateHelper.getDayOrdinal = function (d) {
        var dayMS = 24 * 60 * 60 * 1000;
        return (d.valueOf() - DateHelper.yearStart(d).valueOf()) / dayMS;
    };
    DateHelper.getWeekNumber = function (d) {
        var currThursday = d.getDay() === 4 ? d : DateHelper.addDay(d, 4 - d.getDay());
        var ordinal = DateHelper.getDayOrdinal(currThursday);
        return Math.trunc(ordinal / 7) + 1;
    };
    DateHelper.isSameDay = function (d1, d2) {
        return (d1.getFullYear() === d2.getFullYear() &&
            d1.getMonth() === d2.getMonth() &&
            d1.getDate() === d2.getDate());
    };
    DateHelper.toDateObject = function (date, dateFormat) {
        if (typeof date === "string") {
            return stringToDate(date, dateFormat);
        }
        else {
            return new Date(date);
        }
    };
    DateHelper.nullTimestampDate = new Date(0);
    return DateHelper;
}());


/***/ }),
/* 30 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
var core_1 = __webpack_require__(0);
var dom_1 = __webpack_require__(1);
var common_1 = __webpack_require__(2);
var ScaleSeria_1 = __webpack_require__(17);
var Bar = /** @class */ (function (_super) {
    __extends(Bar, _super);
    function Bar() {
        var _this = _super !== null && _super.apply(this, arguments) || this;
        _this._shift = 0;
        return _this;
    }
    Bar.prototype.addScale = function (type, scale) {
        _super.prototype.addScale.call(this, type, scale);
        scale.addPadding();
    };
    Bar.prototype.seriesShift = function (shift) {
        this._shift = shift;
        return this.config.barWidth;
    };
    Bar.prototype.paint = function (width, height, prev) {
        _super.prototype.paint.call(this, width, height);
        var getChartAriaAttrs = function (cfg) { return ({
            "aria-label": "chart ".concat(cfg.value || ""),
        }); };
        if (!this.config.active) {
            return null;
        }
        var svg = [];
        if (this._gradient) {
            svg.push((0, dom_1.sv)("defs", [this._gradient()]));
        }
        var form = this._getForm(this._points, width, height, prev);
        svg = svg.concat(form);
        return (0, dom_1.sv)("g", __assign(__assign({ class: "seria", _key: this.id }, getChartAriaAttrs(this.config)), { tabindex: 0 }), svg);
    };
    Bar.prototype.getTooltipType = function (_id, _x, y) {
        if (this.config.baseLine !== undefined && this._baseLinePosition < y) {
            return "bot";
        }
        return "top";
    };
    Bar.prototype._getClosestDist = function (x, y, px, py) {
        if (this.config.stacked && y < py) {
            return Infinity;
        }
        return Math.abs(x - px);
    };
    Bar.prototype._path = function (item, prev) {
        item[0] += this._shift;
        return "\nM ".concat(item[0] - this.config.barWidth / 2, " ").concat(prev, "\nV ").concat(item[1], "\nh ").concat(this.config.barWidth, "\nV ").concat(prev);
    };
    Bar.prototype._base = function (height) {
        var baseLine = this.config.baseLine;
        return (this._baseLinePosition =
            baseLine !== undefined ? this.yScale.point(baseLine) * height : height - 1);
    };
    Bar.prototype._text = function (item, prev, rotate) {
        var x = item[0];
        var y = (prev + item[1]) / 2;
        var canRotate = rotate && !isNaN(rotate);
        return {
            x: x,
            y: y,
            class: "bar-text",
            transform: canRotate ? "rotate(".concat(rotate, " ").concat(x, " ").concat(y, ")") : "",
        };
    };
    Bar.prototype._getForm = function (points, _width, height, prev) {
        var _this = this;
        var getPointAriaLabel = function (barType, item, baseLine) {
            if (baseLine === void 0) { baseLine = 0; }
            var x = item[3];
            var yStart = baseLine;
            var yEnd = item[4];
            if (baseLine > yEnd) {
                yStart = yEnd;
                yEnd = baseLine;
            }
            return barType === "xbar"
                ? "bar y=".concat(x, ", x from ").concat(yStart, " to ").concat(yEnd)
                : "bar x=".concat(x, ", y from ").concat(yStart, " to ").concat(yEnd);
        };
        var getPointAriaAttrs = function (barType, item, baseLine) { return ({
            role: "graphics-symbol",
            "aria-roledescription": "bar",
            "aria-label": getPointAriaLabel(barType, item, baseLine),
        }); };
        var _a = this.config, baseLine = _a.baseLine, fill = _a.fill, alpha = _a.alpha, showText = _a.showText, showTextTemplate = _a.showTextTemplate, showTextRotate = _a.showTextRotate;
        var svg = [];
        var base = this._base(height);
        var getPrev = function (index) { return (!prev ? base : prev[index][1]); };
        var series = points.map(function (item, index) {
            return (0, dom_1.sv)("path", __assign(__assign({ _key: "seria" + _this.config.id + index, _ref: (0, common_1.calcPointRef)(item[2], _this.config.id), d: _this._path(item, getPrev(index)), class: _this._getCss(), fill: fill, onclick: [_this._handlers.onclick, item[2], _this.config.value], onmousemove: [_this._handlers.onmousemove, item[2], _this.config.id], onmouseleave: [_this._handlers.onmouseleave, item[2], _this.config.id], "fill-opacity": alpha }, getPointAriaAttrs(_this.config.type, item, baseLine)), { tabindex: 0 }));
        });
        svg.push.apply(svg, series);
        if ((showText || showTextTemplate || showTextRotate) && showText !== false) {
            var isWrite_1 = function (item, index) { return Math.abs(getPrev(index) - item[1]) > 16; }; // hide text, where height < 16
            var text = points.map(function (item, index) {
                var value = _this._getText(item);
                return isWrite_1(item, index)
                    ? (0, dom_1.sv)("text", __assign(__assign({}, _this._text(item, getPrev(index), showTextRotate)), { "aria-hidden": "true" }), [
                        showTextTemplate
                            ? (0, common_1.verticalCenteredText)(showTextTemplate(value))
                            : (0, common_1.verticalCenteredText)(value),
                    ])
                    : null;
            });
            svg.push.apply(svg, text);
        }
        return svg;
    };
    Bar.prototype._getText = function (item) {
        return item[4].toString();
    };
    Bar.prototype._setDefaults = function (config) {
        var defaults = {
            barWidth: 30,
            alpha: 1,
            active: true,
            tooltip: true,
            pointType: "empty",
        };
        this.config = __assign(__assign({}, defaults), config);
        var point = this.config.pointType;
        var color = this.config.pointColor || this.config.color;
        if (point) {
            this.config.pointType = point;
            this._drawPointType = this._getPointType(point, color);
        }
        if (this.config.gradient) {
            var id_1 = "gradient" + (0, core_1.uid)();
            var gradient_1 = this.config.gradient(this.config.fill);
            this._gradient = function () { return (0, common_1.linearGradient)(gradient_1, id_1); };
            this.config.fill = "url(#".concat(id_1, ")");
        }
    };
    return Bar;
}(ScaleSeria_1.default));
exports.default = Bar;


/***/ }),
/* 31 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
function spline(initPoints, link) {
    var len = initPoints.length;
    var points;
    if (len < 3) {
        points = initPoints;
    }
    else {
        var p0 = initPoints[0];
        var p1 = initPoints[0];
        var p2 = initPoints[1];
        var p3 = initPoints[2];
        points = [initPoints[0].slice(0, 2)];
        for (var i = 1; i < len; i++) {
            points.push([
                (-p0[0] + 6 * p1[0] + p2[0]) / 6,
                (-p0[1] + 6 * p1[1] + p2[1]) / 6,
                (p1[0] + 6 * p2[0] - p3[0]) / 6,
                (p1[1] + 6 * p2[1] - p3[1]) / 6,
                p2[0],
                p2[1],
            ]);
            p0 = p1;
            p1 = p2;
            p2 = p3;
            p3 = initPoints[i + 2] || p3;
        }
    }
    var d = "";
    for (var i = 0; i < points.length; i++) {
        var point = points[i];
        var n = point.length;
        if (!i) {
            d += link ? "L" : "M";
            d += n === 5 ? "".concat(point[0], " ").concat(point[1]) : "".concat(point[n - 2], " ").concat(point[n - 1]);
        }
        else if (n > 5) {
            d += "C".concat(point[0], " ").concat(point[1], "\n\t\t\t\t").concat(point[2], " ").concat(point[3], "\n\t\t\t\t").concat(point[4], " ").concat(point[5]);
        }
        else if (n === 5) {
            d += "L".concat(point[0], " ").concat(point[1]);
        }
        else {
            d += "S".concat(point[0], " ").concat(point[1], "\n\t\t\t\t").concat(point[2], " ").concat(point[3]);
        }
    }
    return d;
}
exports.default = spline;


/***/ }),
/* 32 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
var locale = {
    apply: "apply",
    reject: "reject",
};
exports.default = locale;


/***/ }),
/* 33 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.blockScreen = void 0;
function blockKeys(e) {
    var active = document.activeElement;
    if (active.classList.contains("dhx_alert__apply-button") && e.key === "Enter") {
        return;
    }
    if (!active.classList.contains("dhx_alert__confirm-reject") &&
        !active.classList.contains("dhx_alert__confirm-aply")) {
        e.preventDefault();
    }
}
function blockScreen(css) {
    var blocker = document.createElement("div");
    blocker.className = "dhx_alert__overlay " + (css || "");
    document.body.appendChild(blocker);
    document.addEventListener("keydown", blockKeys);
    return function () {
        document.body.removeChild(blocker);
        document.removeEventListener("keydown", blockKeys);
    };
}
exports.blockScreen = blockScreen;


/***/ }),
/* 34 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(35);
__webpack_require__(36);
__webpack_require__(37);
__webpack_require__(38);
__webpack_require__(39);
module.exports = __webpack_require__(40);


/***/ }),
/* 35 */
/***/ (function(module, exports) {

Object.values = Object.values
    ? Object.values
    : function (obj) {
        var allowedTypes = [
            "[object String]",
            "[object Object]",
            "[object Array]",
            "[object Function]",
        ];
        var objType = Object.prototype.toString.call(obj);
        if (obj === null || typeof obj === "undefined") {
            throw new TypeError("Cannot convert undefined or null to object");
        }
        else if (!~allowedTypes.indexOf(objType)) {
            return [];
        }
        else {
            // if ES6 is supported
            if (Object.keys) {
                return Object.keys(obj).map(function (key) {
                    return obj[key];
                });
            }
            var result = [];
            for (var prop in obj) {
                if (obj.hasOwnProperty(prop)) {
                    result.push(obj[prop]);
                }
            }
            return result;
        }
    };
if (!Object.assign) {
    Object.defineProperty(Object, "assign", {
        enumerable: false,
        configurable: true,
        writable: true,
        value: function (target) {
            "use strict";
            var args = [];
            for (var _i = 1; _i < arguments.length; _i++) {
                args[_i - 1] = arguments[_i];
            }
            if (target === undefined || target === null) {
                throw new TypeError("Cannot convert first argument to object");
            }
            var to = Object(target);
            for (var i = 0; i < args.length; i++) {
                var nextSource = args[i];
                if (nextSource === undefined || nextSource === null) {
                    continue;
                }
                var keysArray = Object.keys(Object(nextSource));
                for (var nextIndex = 0, len = keysArray.length; nextIndex < len; nextIndex++) {
                    var nextKey = keysArray[nextIndex];
                    var desc = Object.getOwnPropertyDescriptor(nextSource, nextKey);
                    if (desc !== undefined && desc.enumerable) {
                        to[nextKey] = nextSource[nextKey];
                    }
                }
            }
            return to;
        },
    });
}


/***/ }),
/* 36 */
/***/ (function(module, exports) {

/* eslint-disable prefer-rest-params */
/* eslint-disable @typescript-eslint/unbound-method */
// eslint-disable-next-line @typescript-eslint/unbound-method
if (!Array.prototype.includes) {
    Object.defineProperty(Array.prototype, "includes", {
        value: function (searchElement, fromIndex) {
            if (this == null) {
                throw new TypeError('"this" is null or not defined');
            }
            // 1. Let O be ? ToObject(this value).
            var o = Object(this);
            // 2. Let len be ? ToLength(? Get(O, "length")).
            var len = o.length >>> 0;
            // 3. If len is 0, return false.
            if (len === 0) {
                return false;
            }
            // 4. Let n be ? ToInteger(fromIndex).
            //    (If fromIndex is undefined, this step produces the value 0.)
            var n = fromIndex | 0;
            // 5. If n ≥ 0, then
            //  a. Let k be n.
            // 6. Else n < 0,
            //  a. Let k be len + n.
            //  b. If k < 0, let k be 0.
            var k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);
            function sameValueZero(x, y) {
                return x === y || (typeof x === "number" && typeof y === "number" && isNaN(x) && isNaN(y));
            }
            // 7. Repeat, while k < len
            while (k < len) {
                // a. Let elementK be the result of ? Get(O, ! ToString(k)).
                // b. If SameValueZero(searchElement, elementK) is true, return true.
                if (sameValueZero(o[k], searchElement)) {
                    return true;
                }
                // c. Increase k by 1.
                k++;
            }
            // 8. Return false
            return false;
        },
        configurable: true,
        writable: true,
    });
}
// https://tc39.github.io/ecma262/#sec-array.prototype.find
if (!Array.prototype.find) {
    Object.defineProperty(Array.prototype, "find", {
        value: function (predicate) {
            // 1. Let O be ? ToObject(this value).
            if (this == null) {
                throw new TypeError('"this" is null or not defined');
            }
            var o = Object(this);
            // 2. Let len be ? ToLength(? Get(O, "length")).
            var len = o.length >>> 0;
            // 3. If IsCallable(predicate) is false, throw a TypeError exception.
            if (typeof predicate !== "function") {
                throw new TypeError("predicate must be a function");
            }
            // 4. If thisArg was supplied, let T be thisArg; else let T be undefined.
            var thisArg = arguments[1];
            // 5. Let k be 0.
            var k = 0;
            // 6. Repeat, while k < len
            while (k < len) {
                // a. Let Pk be ! ToString(k).
                // b. Let kValue be ? Get(O, Pk).
                // c. Let testResult be ToBoolean(? Call(predicate, T, « kValue, k, O »)).
                // d. If testResult is true, return kValue.
                var kValue = o[k];
                if (predicate.call(thisArg, kValue, k, o)) {
                    return kValue;
                }
                // e. Increase k by 1.
                k++;
            }
            // 7. Return undefined.
            return undefined;
        },
        configurable: true,
        writable: true,
    });
}
if (!Array.prototype.findIndex) {
    Array.prototype.findIndex = function (predicate) {
        if (this == null) {
            throw new TypeError("Array.prototype.findIndex called on null or undefined");
        }
        if (typeof predicate !== "function") {
            throw new TypeError("predicate must be a function");
        }
        var list = Object(this);
        var length = list.length >>> 0;
        var thisArg = arguments[1];
        var value;
        for (var i = 0; i < length; i++) {
            value = list[i];
            if (predicate.call(thisArg, value, i, list)) {
                return i;
            }
        }
        return -1;
    };
}


/***/ }),
/* 37 */
/***/ (function(module, exports) {

if (!String.prototype.includes) {
    String.prototype.includes = function (search, start) {
        "use strict";
        if (typeof start !== "number") {
            start = 0;
        }
        if (start + search.length > this.length) {
            return false;
        }
        else {
            return this.indexOf(search, start) !== -1;
        }
    };
}
if (!String.prototype.startsWith) {
    Object.defineProperty(String.prototype, "startsWith", {
        enumerable: false,
        configurable: true,
        writable: true,
        value: function (searchString, position) {
            position = position || 0;
            return this.indexOf(searchString, position) === position;
        },
    });
}
if (!String.prototype.padStart) {
    String.prototype.padStart = function padStart(targetLength, padString) {
        targetLength = targetLength >> 0;
        padString = String(padString || " ");
        if (this.length > targetLength) {
            return String(this);
        }
        else {
            targetLength = targetLength - this.length;
            if (targetLength > padString.length) {
                padString += padString.repeat(targetLength / padString.length);
            }
            return padString.slice(0, targetLength) + String(this);
        }
    };
}
if (!String.prototype.padEnd) {
    String.prototype.padEnd = function padEnd(targetLength, padString) {
        targetLength = targetLength >> 0;
        padString = String(padString || " ");
        if (this.length > targetLength) {
            return String(this);
        }
        else {
            targetLength = targetLength - this.length;
            if (targetLength > padString.length) {
                padString += padString.repeat(targetLength / padString.length);
            }
            return String(this) + padString.slice(0, targetLength);
        }
    };
}


/***/ }),
/* 38 */
/***/ (function(module, exports) {

/* eslint-disable @typescript-eslint/no-this-alias */
/* eslint-disable prefer-rest-params */
/* eslint-disable @typescript-eslint/unbound-method */
if (Element && !Element.prototype.matches) {
    var proto = Element.prototype;
    proto.matches =
        proto.matchesSelector ||
            proto.mozMatchesSelector ||
            proto.msMatchesSelector ||
            proto.oMatchesSelector ||
            proto.webkitMatchesSelector;
}
// Source: https://github.com/naminho/svg-classlist-polyfill/blob/master/polyfill.js
if (!("classList" in SVGElement.prototype)) {
    Object.defineProperty(SVGElement.prototype, "classList", {
        get: function get() {
            var _this = this;
            return {
                contains: function contains(className) {
                    return _this.className.baseVal.split(" ").indexOf(className) !== -1;
                },
                add: function add(className) {
                    return _this.setAttribute("class", _this.getAttribute("class") + " " + className);
                },
                remove: function remove(className) {
                    var removedClass = _this
                        .getAttribute("class")
                        .replace(new RegExp("(\\s|^)".concat(className, "(\\s|$)"), "g"), "$2");
                    if (_this.classList.contains(className)) {
                        _this.setAttribute("class", removedClass);
                    }
                },
                toggle: function toggle(className) {
                    if (this.contains(className)) {
                        this.remove(className);
                    }
                    else {
                        this.add(className);
                    }
                },
            };
        },
        configurable: true,
    });
}
// Source: https://github.com/tc39/proposal-object-values-entries/blob/master/polyfill.js
if (!Object.entries) {
    var reduce_1 = Function.bind.call(Function.call, Array.prototype.reduce);
    var isEnumerable_1 = Function.bind.call(Function.call, Object.prototype.propertyIsEnumerable);
    var concat_1 = Function.bind.call(Function.call, Array.prototype.concat);
    Object.entries = function entries(O) {
        return reduce_1(Object.keys(O), function (e, k) { return concat_1(e, typeof k === "string" && isEnumerable_1(O, k) ? [[k, O[k]]] : []); }, []);
    };
}
// Source: https://gist.github.com/rockinghelvetica/00b9f7b5c97a16d3de75ba99192ff05c
if (!Event.prototype.composedPath) {
    Event.prototype.composedPath = function () {
        if (this.path) {
            return this.path;
        }
        var target = this.target;
        this.path = [];
        while (target.parentNode !== null) {
            this.path.push(target);
            target = target.parentNode;
        }
        this.path.push(document, window);
        return this.path;
    };
}


/***/ }),
/* 39 */
/***/ (function(module, exports) {

Math.sign =
    Math.sign ||
        function (x) {
            x = +x;
            if (x === 0 || isNaN(x)) {
                return x;
            }
            return x > 0 ? 1 : -1;
        };


/***/ }),
/* 40 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.Chart = exports.methods = exports.setTheme = exports.awaitRedraw = exports.resizeHandler = exports.vmdom = void 0;
__webpack_require__(41);
__webpack_require__(42);
// HELPERS
var dom_1 = __webpack_require__(1);
exports.vmdom = { sv: dom_1.sv };
var dom_2 = __webpack_require__(1);
Object.defineProperty(exports, "resizeHandler", { enumerable: true, get: function () { return dom_2.resizeHandler; } });
Object.defineProperty(exports, "awaitRedraw", { enumerable: true, get: function () { return dom_2.awaitRedraw; } });
Object.defineProperty(exports, "setTheme", { enumerable: true, get: function () { return dom_2.setTheme; } });
var ts_data_1 = __webpack_require__(7);
Object.defineProperty(exports, "methods", { enumerable: true, get: function () { return ts_data_1.methods; } });
// WIDGETS
var Chart_1 = __webpack_require__(57);
Object.defineProperty(exports, "Chart", { enumerable: true, get: function () { return Chart_1.Chart; } });


/***/ }),
/* 41 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),
/* 42 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),
/* 43 */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(global) {var scope = (typeof global !== "undefined" && global) ||
            (typeof self !== "undefined" && self) ||
            window;
var apply = Function.prototype.apply;

// DOM APIs, for completeness

exports.setTimeout = function() {
  return new Timeout(apply.call(setTimeout, scope, arguments), clearTimeout);
};
exports.setInterval = function() {
  return new Timeout(apply.call(setInterval, scope, arguments), clearInterval);
};
exports.clearTimeout =
exports.clearInterval = function(timeout) {
  if (timeout) {
    timeout.close();
  }
};

function Timeout(id, clearFn) {
  this._id = id;
  this._clearFn = clearFn;
}
Timeout.prototype.unref = Timeout.prototype.ref = function() {};
Timeout.prototype.close = function() {
  this._clearFn.call(scope, this._id);
};

// Does not start the time, just sets up the members needed.
exports.enroll = function(item, msecs) {
  clearTimeout(item._idleTimeoutId);
  item._idleTimeout = msecs;
};

exports.unenroll = function(item) {
  clearTimeout(item._idleTimeoutId);
  item._idleTimeout = -1;
};

exports._unrefActive = exports.active = function(item) {
  clearTimeout(item._idleTimeoutId);

  var msecs = item._idleTimeout;
  if (msecs >= 0) {
    item._idleTimeoutId = setTimeout(function onTimeout() {
      if (item._onTimeout)
        item._onTimeout();
    }, msecs);
  }
};

// setimmediate attaches itself to the global object
__webpack_require__(44);
// On some exotic environments, it's not clear which object `setimmediate` was
// able to install onto.  Search each possibility in the same order as the
// `setimmediate` library.
exports.setImmediate = (typeof self !== "undefined" && self.setImmediate) ||
                       (typeof global !== "undefined" && global.setImmediate) ||
                       (this && this.setImmediate);
exports.clearImmediate = (typeof self !== "undefined" && self.clearImmediate) ||
                         (typeof global !== "undefined" && global.clearImmediate) ||
                         (this && this.clearImmediate);

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(12)))

/***/ }),
/* 44 */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(global, process) {(function (global, undefined) {
    "use strict";

    if (global.setImmediate) {
        return;
    }

    var nextHandle = 1; // Spec says greater than zero
    var tasksByHandle = {};
    var currentlyRunningATask = false;
    var doc = global.document;
    var registerImmediate;

    function setImmediate(callback) {
      // Callback can either be a function or a string
      if (typeof callback !== "function") {
        callback = new Function("" + callback);
      }
      // Copy function arguments
      var args = new Array(arguments.length - 1);
      for (var i = 0; i < args.length; i++) {
          args[i] = arguments[i + 1];
      }
      // Store and register the task
      var task = { callback: callback, args: args };
      tasksByHandle[nextHandle] = task;
      registerImmediate(nextHandle);
      return nextHandle++;
    }

    function clearImmediate(handle) {
        delete tasksByHandle[handle];
    }

    function run(task) {
        var callback = task.callback;
        var args = task.args;
        switch (args.length) {
        case 0:
            callback();
            break;
        case 1:
            callback(args[0]);
            break;
        case 2:
            callback(args[0], args[1]);
            break;
        case 3:
            callback(args[0], args[1], args[2]);
            break;
        default:
            callback.apply(undefined, args);
            break;
        }
    }

    function runIfPresent(handle) {
        // From the spec: "Wait until any invocations of this algorithm started before this one have completed."
        // So if we're currently running a task, we'll need to delay this invocation.
        if (currentlyRunningATask) {
            // Delay by doing a setTimeout. setImmediate was tried instead, but in Firefox 7 it generated a
            // "too much recursion" error.
            setTimeout(runIfPresent, 0, handle);
        } else {
            var task = tasksByHandle[handle];
            if (task) {
                currentlyRunningATask = true;
                try {
                    run(task);
                } finally {
                    clearImmediate(handle);
                    currentlyRunningATask = false;
                }
            }
        }
    }

    function installNextTickImplementation() {
        registerImmediate = function(handle) {
            process.nextTick(function () { runIfPresent(handle); });
        };
    }

    function canUsePostMessage() {
        // The test against `importScripts` prevents this implementation from being installed inside a web worker,
        // where `global.postMessage` means something completely different and can't be used for this purpose.
        if (global.postMessage && !global.importScripts) {
            var postMessageIsAsynchronous = true;
            var oldOnMessage = global.onmessage;
            global.onmessage = function() {
                postMessageIsAsynchronous = false;
            };
            global.postMessage("", "*");
            global.onmessage = oldOnMessage;
            return postMessageIsAsynchronous;
        }
    }

    function installPostMessageImplementation() {
        // Installs an event handler on `global` for the `message` event: see
        // * https://developer.mozilla.org/en/DOM/window.postMessage
        // * http://www.whatwg.org/specs/web-apps/current-work/multipage/comms.html#crossDocumentMessages

        var messagePrefix = "setImmediate$" + Math.random() + "$";
        var onGlobalMessage = function(event) {
            if (event.source === global &&
                typeof event.data === "string" &&
                event.data.indexOf(messagePrefix) === 0) {
                runIfPresent(+event.data.slice(messagePrefix.length));
            }
        };

        if (global.addEventListener) {
            global.addEventListener("message", onGlobalMessage, false);
        } else {
            global.attachEvent("onmessage", onGlobalMessage);
        }

        registerImmediate = function(handle) {
            global.postMessage(messagePrefix + handle, "*");
        };
    }

    function installMessageChannelImplementation() {
        var channel = new MessageChannel();
        channel.port1.onmessage = function(event) {
            var handle = event.data;
            runIfPresent(handle);
        };

        registerImmediate = function(handle) {
            channel.port2.postMessage(handle);
        };
    }

    function installReadyStateChangeImplementation() {
        var html = doc.documentElement;
        registerImmediate = function(handle) {
            // Create a <script> element; its readystatechange event will be fired asynchronously once it is inserted
            // into the document. Do so, thus queuing up the task. Remember to clean up once it's been called.
            var script = doc.createElement("script");
            script.onreadystatechange = function () {
                runIfPresent(handle);
                script.onreadystatechange = null;
                html.removeChild(script);
                script = null;
            };
            html.appendChild(script);
        };
    }

    function installSetTimeoutImplementation() {
        registerImmediate = function(handle) {
            setTimeout(runIfPresent, 0, handle);
        };
    }

    // If supported, we should attach to the prototype of global, since that is where setTimeout et al. live.
    var attachTo = Object.getPrototypeOf && Object.getPrototypeOf(global);
    attachTo = attachTo && attachTo.setTimeout ? attachTo : global;

    // Don't get fooled by e.g. browserify environments.
    if ({}.toString.call(global.process) === "[object process]") {
        // For Node.js before 0.9
        installNextTickImplementation();

    } else if (canUsePostMessage()) {
        // For non-IE10 modern browsers
        installPostMessageImplementation();

    } else if (global.MessageChannel) {
        // For web workers, where supported
        installMessageChannelImplementation();

    } else if (doc && "onreadystatechange" in doc.createElement("script")) {
        // For IE 6–8
        installReadyStateChangeImplementation();

    } else {
        // For older browsers
        installSetTimeoutImplementation();
    }

    attachTo.setImmediate = setImmediate;
    attachTo.clearImmediate = clearImmediate;
}(typeof self === "undefined" ? typeof global === "undefined" ? this : global : self));

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(12), __webpack_require__(45)))

/***/ }),
/* 45 */
/***/ (function(module, exports) {

// shim for using process in browser
var process = module.exports = {};

// cached from whatever global is present so that test runners that stub it
// don't break things.  But we need to wrap it in a try catch in case it is
// wrapped in strict mode code which doesn't define any globals.  It's inside a
// function because try/catches deoptimize in certain engines.

var cachedSetTimeout;
var cachedClearTimeout;

function defaultSetTimout() {
    throw new Error('setTimeout has not been defined');
}
function defaultClearTimeout () {
    throw new Error('clearTimeout has not been defined');
}
(function () {
    try {
        if (typeof setTimeout === 'function') {
            cachedSetTimeout = setTimeout;
        } else {
            cachedSetTimeout = defaultSetTimout;
        }
    } catch (e) {
        cachedSetTimeout = defaultSetTimout;
    }
    try {
        if (typeof clearTimeout === 'function') {
            cachedClearTimeout = clearTimeout;
        } else {
            cachedClearTimeout = defaultClearTimeout;
        }
    } catch (e) {
        cachedClearTimeout = defaultClearTimeout;
    }
} ())
function runTimeout(fun) {
    if (cachedSetTimeout === setTimeout) {
        //normal enviroments in sane situations
        return setTimeout(fun, 0);
    }
    // if setTimeout wasn't available but was latter defined
    if ((cachedSetTimeout === defaultSetTimout || !cachedSetTimeout) && setTimeout) {
        cachedSetTimeout = setTimeout;
        return setTimeout(fun, 0);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedSetTimeout(fun, 0);
    } catch(e){
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't trust the global object when called normally
            return cachedSetTimeout.call(null, fun, 0);
        } catch(e){
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error
            return cachedSetTimeout.call(this, fun, 0);
        }
    }


}
function runClearTimeout(marker) {
    if (cachedClearTimeout === clearTimeout) {
        //normal enviroments in sane situations
        return clearTimeout(marker);
    }
    // if clearTimeout wasn't available but was latter defined
    if ((cachedClearTimeout === defaultClearTimeout || !cachedClearTimeout) && clearTimeout) {
        cachedClearTimeout = clearTimeout;
        return clearTimeout(marker);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedClearTimeout(marker);
    } catch (e){
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't  trust the global object when called normally
            return cachedClearTimeout.call(null, marker);
        } catch (e){
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error.
            // Some versions of I.E. have different rules for clearTimeout vs setTimeout
            return cachedClearTimeout.call(this, marker);
        }
    }



}
var queue = [];
var draining = false;
var currentQueue;
var queueIndex = -1;

function cleanUpNextTick() {
    if (!draining || !currentQueue) {
        return;
    }
    draining = false;
    if (currentQueue.length) {
        queue = currentQueue.concat(queue);
    } else {
        queueIndex = -1;
    }
    if (queue.length) {
        drainQueue();
    }
}

function drainQueue() {
    if (draining) {
        return;
    }
    var timeout = runTimeout(cleanUpNextTick);
    draining = true;

    var len = queue.length;
    while(len) {
        currentQueue = queue;
        queue = [];
        while (++queueIndex < len) {
            if (currentQueue) {
                currentQueue[queueIndex].run();
            }
        }
        queueIndex = -1;
        len = queue.length;
    }
    currentQueue = null;
    draining = false;
    runClearTimeout(timeout);
}

process.nextTick = function (fun) {
    var args = new Array(arguments.length - 1);
    if (arguments.length > 1) {
        for (var i = 1; i < arguments.length; i++) {
            args[i - 1] = arguments[i];
        }
    }
    queue.push(new Item(fun, args));
    if (queue.length === 1 && !draining) {
        runTimeout(drainQueue);
    }
};

// v8 likes predictible objects
function Item(fun, array) {
    this.fun = fun;
    this.array = array;
}
Item.prototype.run = function () {
    this.fun.apply(null, this.array);
};
process.title = 'browser';
process.browser = true;
process.env = {};
process.argv = [];
process.version = ''; // empty string to avoid regexp issues
process.versions = {};

function noop() {}

process.on = noop;
process.addListener = noop;
process.once = noop;
process.off = noop;
process.removeListener = noop;
process.removeAllListeners = noop;
process.emit = noop;
process.prependListener = noop;
process.prependOnceListener = noop;

process.listeners = function (name) { return [] }

process.binding = function (name) {
    throw new Error('process.binding is not supported');
};

process.cwd = function () { return '/' };
process.chdir = function (dir) {
    throw new Error('process.chdir is not supported');
};
process.umask = function() { return 0; };


/***/ }),
/* 46 */
/***/ (function(module, exports, __webpack_require__) {

/**
* Copyright (c) 2017, Leon Sorokin
* All rights reserved. (MIT Licensed)
*
* domvm.js (DOM ViewModel)
* A thin, fast, dependency-free vdom view layer
* @preserve https://github.com/leeoniya/domvm (v3.2.6, micro build)
*/

(function (global, factory) {
	 true ? module.exports = factory() :
	undefined;
}(this, (function () { 'use strict';

// NOTE: if adding a new *VNode* type, make it < COMMENT and renumber rest.
// There are some places that test <= COMMENT to assert if node is a VNode

// VNode types
var ELEMENT	= 1;
var TEXT		= 2;
var COMMENT	= 3;

// placeholder types
var VVIEW		= 4;
var VMODEL		= 5;

var ENV_DOM = typeof window !== "undefined";
var win = ENV_DOM ? window : {};
var rAF = win.requestAnimationFrame;

var emptyObj = {};

function noop() {}

var isArr = Array.isArray;

function isSet(val) {
	return val != null;
}

function isPlainObj(val) {
	return val != null && val.constructor === Object;		//  && typeof val === "object"
}

function insertArr(targ, arr, pos, rem) {
	targ.splice.apply(targ, [pos, rem].concat(arr));
}

function isVal(val) {
	var t = typeof val;
	return t === "string" || t === "number";
}

function isFunc(val) {
	return typeof val === "function";
}

function isProm(val) {
	return typeof val === "object" && isFunc(val.then);
}



function assignObj(targ) {
	var args = arguments;

	for (var i = 1; i < args.length; i++)
		{ for (var k in args[i])
			{ targ[k] = args[i][k]; } }

	return targ;
}

// export const defProp = Object.defineProperty;

function deepSet(targ, path, val) {
	var seg;

	while (seg = path.shift()) {
		if (path.length === 0)
			{ targ[seg] = val; }
		else
			{ targ[seg] = targ = targ[seg] || {}; }
	}
}

/*
export function deepUnset(targ, path) {
	var seg;

	while (seg = path.shift()) {
		if (path.length === 0)
			targ[seg] = val;
		else
			targ[seg] = targ = targ[seg] || {};
	}
}
*/

function sliceArgs(args, offs) {
	var arr = [];
	for (var i = offs; i < args.length; i++)
		{ arr.push(args[i]); }
	return arr;
}

function cmpObj(a, b) {
	for (var i in a)
		{ if (a[i] !== b[i])
			{ return false; } }

	return true;
}

function cmpArr(a, b) {
	var alen = a.length;

	if (b.length !== alen)
		{ return false; }

	for (var i = 0; i < alen; i++)
		{ if (a[i] !== b[i])
			{ return false; } }

	return true;
}

// https://github.com/darsain/raft
// rAF throttler, aggregates multiple repeated redraw calls within single animframe
function raft(fn) {
	if (!rAF)
		{ return fn; }

	var id, ctx, args;

	function call() {
		id = 0;
		fn.apply(ctx, args);
	}

	return function() {
		ctx = this;
		args = arguments;
		if (!id) { id = rAF(call); }
	};
}

function curry(fn, args, ctx) {
	return function() {
		return fn.apply(ctx, args);
	};
}

/*
export function prop(val, cb, ctx, args) {
	return function(newVal, execCb) {
		if (newVal !== undefined && newVal !== val) {
			val = newVal;
			execCb !== false && isFunc(cb) && cb.apply(ctx, args);
		}

		return val;
	};
}
*/

/*
// adapted from https://github.com/Olical/binary-search
export function binaryKeySearch(list, item) {
    var min = 0;
    var max = list.length - 1;
    var guess;

	var bitwise = (max <= 2147483647) ? true : false;
	if (bitwise) {
		while (min <= max) {
			guess = (min + max) >> 1;
			if (list[guess].key === item) { return guess; }
			else {
				if (list[guess].key < item) { min = guess + 1; }
				else { max = guess - 1; }
			}
		}
	} else {
		while (min <= max) {
			guess = Math.floor((min + max) / 2);
			if (list[guess].key === item) { return guess; }
			else {
				if (list[guess].key < item) { min = guess + 1; }
				else { max = guess - 1; }
			}
		}
	}

    return -1;
}
*/

// https://en.wikipedia.org/wiki/Longest_increasing_subsequence
// impl borrowed from https://github.com/ivijs/ivi
function longestIncreasingSubsequence(a) {
	var p = a.slice();
	var result = [];
	result.push(0);
	var u;
	var v;

	for (var i = 0, il = a.length; i < il; ++i) {
		var j = result[result.length - 1];
		if (a[j] < a[i]) {
			p[i] = j;
			result.push(i);
			continue;
		}

		u = 0;
		v = result.length - 1;

		while (u < v) {
			var c = ((u + v) / 2) | 0;
			if (a[result[c]] < a[i]) {
				u = c + 1;
			} else {
				v = c;
			}
		}

		if (a[i] < a[result[u]]) {
			if (u > 0) {
				p[i] = result[u - 1];
			}
			result[u] = i;
		}
	}

	u = result.length;
	v = result[u - 1];

	while (u-- > 0) {
		result[u] = v;
		v = p[v];
	}

	return result;
}

// based on https://github.com/Olical/binary-search
function binaryFindLarger(item, list) {
	var min = 0;
	var max = list.length - 1;
	var guess;

	var bitwise = (max <= 2147483647) ? true : false;
	if (bitwise) {
		while (min <= max) {
			guess = (min + max) >> 1;
			if (list[guess] === item) { return guess; }
			else {
				if (list[guess] < item) { min = guess + 1; }
				else { max = guess - 1; }
			}
		}
	} else {
		while (min <= max) {
			guess = Math.floor((min + max) / 2);
			if (list[guess] === item) { return guess; }
			else {
				if (list[guess] < item) { min = guess + 1; }
				else { max = guess - 1; }
			}
		}
	}

	return (min == list.length) ? null : min;

//	return -1;
}

function isEvProp(name) {
	return name[0] === "o" && name[1] === "n";
}

function isSplProp(name) {
	return name[0] === "_";
}

function isStyleProp(name) {
	return name === "style";
}

function repaint(node) {
	node && node.el && node.el.offsetHeight;
}

function isHydrated(vm) {
	return vm.node != null && vm.node.el != null;
}

// tests interactive props where real val should be compared
function isDynProp(tag, attr) {
//	switch (tag) {
//		case "input":
//		case "textarea":
//		case "select":
//		case "option":
			switch (attr) {
				case "value":
				case "checked":
				case "selected":
//				case "selectedIndex":
					return true;
			}
//	}

	return false;
}

function getVm(n) {
	n = n || emptyObj;
	while (n.vm == null && n.parent)
		{ n = n.parent; }
	return n.vm;
}

function VNode() {}

var VNodeProto = VNode.prototype = {
	constructor: VNode,

	type:	null,

	vm:		null,

	// all this stuff can just live in attrs (as defined) just have getters here for it
	key:	null,
	ref:	null,
	data:	null,
	hooks:	null,
	ns:		null,

	el:		null,

	tag:	null,
	attrs:	null,
	body:	null,

	flags:	0,

	_class:	null,
	_diff:	null,

	// pending removal on promise resolution
	_dead:	false,
	// part of longest increasing subsequence?
	_lis:	false,

	idx:	null,
	parent:	null,

	/*
	// break out into optional fluent module
	key:	function(val) { this.key	= val; return this; },
	ref:	function(val) { this.ref	= val; return this; },		// deep refs
	data:	function(val) { this.data	= val; return this; },
	hooks:	function(val) { this.hooks	= val; return this; },		// h("div").hooks()
	html:	function(val) { this.html	= true; return this.body(val); },

	body:	function(val) { this.body	= val; return this; },
	*/
};

function defineText(body) {
	var node = new VNode;
	node.type = TEXT;
	node.body = body;
	return node;
}

// creates a one-shot self-ending stream that redraws target vm
// TODO: if it's already registered by any parent vm, then ignore to avoid simultaneous parent & child refresh

var tagCache = {};

var RE_ATTRS = /\[(\w+)(?:=(\w+))?\]/g;

function cssTag(raw) {
	{
		var cached = tagCache[raw];

		if (cached == null) {
			var tag, id, cls, attr;

			tagCache[raw] = cached = {
				tag:	(tag	= raw.match( /^[-\w]+/))		?	tag[0]						: "div",
				id:		(id		= raw.match( /#([-\w]+)/))		? 	id[1]						: null,
				class:	(cls	= raw.match(/\.([-\w.]+)/))		?	cls[1].replace(/\./g, " ")	: null,
				attrs:	null,
			};

			while (attr = RE_ATTRS.exec(raw)) {
				if (cached.attrs == null)
					{ cached.attrs = {}; }
				cached.attrs[attr[1]] = attr[2] || "";
			}
		}

		return cached;
	}
}

// (de)optimization flags

// forces slow bottom-up removeChild to fire deep willRemove/willUnmount hooks,
var DEEP_REMOVE = 1;
// prevents inserting/removing/reordering of children
var FIXED_BODY = 2;
// enables fast keyed lookup of children via binary search, expects homogeneous keyed body
var KEYED_LIST = 4;
// indicates an vnode match/diff/recycler function for body
var LAZY_LIST = 8;

function initElementNode(tag, attrs, body, flags) {
	var node = new VNode;

	node.type = ELEMENT;

	if (isSet(flags))
		{ node.flags = flags; }

	node.attrs = attrs;

	var parsed = cssTag(tag);

	node.tag = parsed.tag;

	// meh, weak assertion, will fail for id=0, etc.
	if (parsed.id || parsed.class || parsed.attrs) {
		var p = node.attrs || {};

		if (parsed.id && !isSet(p.id))
			{ p.id = parsed.id; }

		if (parsed.class) {
			node._class = parsed.class;		// static class
			p.class = parsed.class + (isSet(p.class) ? (" " + p.class) : "");
		}
		if (parsed.attrs) {
			for (var key in parsed.attrs)
				{ if (!isSet(p[key]))
					{ p[key] = parsed.attrs[key]; } }
		}

//		if (node.attrs !== p)
			node.attrs = p;
	}

	var mergedAttrs = node.attrs;

	if (isSet(mergedAttrs)) {
		if (isSet(mergedAttrs._key))
			{ node.key = mergedAttrs._key; }

		if (isSet(mergedAttrs._ref))
			{ node.ref = mergedAttrs._ref; }

		if (isSet(mergedAttrs._hooks))
			{ node.hooks = mergedAttrs._hooks; }

		if (isSet(mergedAttrs._data))
			{ node.data = mergedAttrs._data; }

		if (isSet(mergedAttrs._flags))
			{ node.flags = mergedAttrs._flags; }

		if (!isSet(node.key)) {
			if (isSet(node.ref))
				{ node.key = node.ref; }
			else if (isSet(mergedAttrs.id))
				{ node.key = mergedAttrs.id; }
			else if (isSet(mergedAttrs.name))
				{ node.key = mergedAttrs.name + (mergedAttrs.type === "radio" || mergedAttrs.type === "checkbox" ? mergedAttrs.value : ""); }
		}
	}

	if (body != null)
		{ node.body = body; }

	return node;
}

function setRef(vm, name, node) {
	var path = ["refs"].concat(name.split("."));
	deepSet(vm, path, node);
}

function setDeepRemove(node) {
	while (node = node.parent)
		{ node.flags |= DEEP_REMOVE; }
}

// vnew, vold
function preProc(vnew, parent, idx, ownVm) {
	if (vnew.type === VMODEL || vnew.type === VVIEW)
		{ return; }

	vnew.parent = parent;
	vnew.idx = idx;
	vnew.vm = ownVm;

	if (vnew.ref != null)
		{ setRef(getVm(vnew), vnew.ref, vnew); }

	var nh = vnew.hooks,
		vh = ownVm && ownVm.hooks;

	if (nh && (nh.willRemove || nh.didRemove) ||
		vh && (vh.willUnmount || vh.didUnmount))
		{ setDeepRemove(vnew); }

	if (isArr(vnew.body))
		{ preProcBody(vnew); }
	else {}
}

function preProcBody(vnew) {
	var body = vnew.body;

	for (var i = 0; i < body.length; i++) {
		var node2 = body[i];

		// remove false/null/undefined
		if (node2 === false || node2 == null)
			{ body.splice(i--, 1); }
		// flatten arrays
		else if (isArr(node2)) {
			insertArr(body, node2, i--, 1);
		}
		else {
			if (node2.type == null)
				{ body[i] = node2 = defineText(""+node2); }

			if (node2.type === TEXT) {
				// remove empty text nodes
				if (node2.body == null || node2.body === "")
					{ body.splice(i--, 1); }
				// merge with previous text node
				else if (i > 0 && body[i-1].type === TEXT) {
					body[i-1].body += node2.body;
					body.splice(i--, 1);
				}
				else
					{ preProc(node2, vnew, i, null); }
			}
			else
				{ preProc(node2, vnew, i, null); }
		}
	}
}

var unitlessProps = {
	animationIterationCount: true,
	boxFlex: true,
	boxFlexGroup: true,
	boxOrdinalGroup: true,
	columnCount: true,
	flex: true,
	flexGrow: true,
	flexPositive: true,
	flexShrink: true,
	flexNegative: true,
	flexOrder: true,
	gridRow: true,
	gridColumn: true,
	order: true,
	lineClamp: true,

	borderImageOutset: true,
	borderImageSlice: true,
	borderImageWidth: true,
	fontWeight: true,
	lineHeight: true,
	opacity: true,
	orphans: true,
	tabSize: true,
	widows: true,
	zIndex: true,
	zoom: true,

	fillOpacity: true,
	floodOpacity: true,
	stopOpacity: true,
	strokeDasharray: true,
	strokeDashoffset: true,
	strokeMiterlimit: true,
	strokeOpacity: true,
	strokeWidth: true
};

function autoPx(name, val) {
	{
		// typeof val === 'number' is faster but fails for numeric strings
		return !isNaN(val) && !unitlessProps[name] ? (val + "px") : val;
	}
}

// assumes if styles exist both are objects or both are strings
function patchStyle(n, o) {
	var ns =     (n.attrs || emptyObj).style;
	var os = o ? (o.attrs || emptyObj).style : null;

	// replace or remove in full
	if (ns == null || isVal(ns))
		{ n.el.style.cssText = ns; }
	else {
		for (var nn in ns) {
			var nv = ns[nn];

			if (os == null || nv != null && nv !== os[nn])
				{ n.el.style[nn] = autoPx(nn, nv); }
		}

		// clean old
		if (os) {
			for (var on in os) {
				if (ns[on] == null)
					{ n.el.style[on] = ""; }
			}
		}
	}
}

var didQueue = [];

function fireHook(hooks, name, o, n, immediate) {
	if (hooks != null) {
		var fn = o.hooks[name];

		if (fn) {
			if (name[0] === "d" && name[1] === "i" && name[2] === "d") {	// did*
				//	console.log(name + " should queue till repaint", o, n);
				immediate ? repaint(o.parent) && fn(o, n) : didQueue.push([fn, o, n]);
			}
			else {		// will*
				//	console.log(name + " may delay by promise", o, n);
				return fn(o, n);		// or pass  done() resolver
			}
		}
	}
}

function drainDidHooks(vm) {
	if (didQueue.length) {
		repaint(vm.node);

		var item;
		while (item = didQueue.shift())
			{ item[0](item[1], item[2]); }
	}
}

var doc = ENV_DOM ? document : null;

function closestVNode(el) {
	while (el._node == null)
		{ el = el.parentNode; }
	return el._node;
}

function createElement(tag, ns) {
	if (ns != null)
		{ return doc.createElementNS(ns, tag); }
	return doc.createElement(tag);
}

function createTextNode(body) {
	return doc.createTextNode(body);
}

function createComment(body) {
	return doc.createComment(body);
}

// ? removes if !recycled
function nextSib(sib) {
	return sib.nextSibling;
}

// ? removes if !recycled
function prevSib(sib) {
	return sib.previousSibling;
}

// TODO: this should collect all deep proms from all hooks and return Promise.all()
function deepNotifyRemove(node) {
	var vm = node.vm;

	var wuRes = vm != null && fireHook(vm.hooks, "willUnmount", vm, vm.data);

	var wrRes = fireHook(node.hooks, "willRemove", node);

	if ((node.flags & DEEP_REMOVE) === DEEP_REMOVE && isArr(node.body)) {
		for (var i = 0; i < node.body.length; i++)
			{ deepNotifyRemove(node.body[i]); }
	}

	return wuRes || wrRes;
}

function _removeChild(parEl, el, immediate) {
	var node = el._node, vm = node.vm;

	if (isArr(node.body)) {
		if ((node.flags & DEEP_REMOVE) === DEEP_REMOVE) {
			for (var i = 0; i < node.body.length; i++)
				{ _removeChild(el, node.body[i].el); }
		}
		else
			{ deepUnref(node); }
	}

	delete el._node;

	parEl.removeChild(el);

	fireHook(node.hooks, "didRemove", node, null, immediate);

	if (vm != null) {
		fireHook(vm.hooks, "didUnmount", vm, vm.data, immediate);
		vm.node = null;
	}
}

// todo: should delay parent unmount() by returning res prom?
function removeChild(parEl, el) {
	var node = el._node;

	// already marked for removal
	if (node._dead) { return; }

	var res = deepNotifyRemove(node);

	if (res != null && isProm(res)) {
		node._dead = true;
		res.then(curry(_removeChild, [parEl, el, true]));
	}
	else
		{ _removeChild(parEl, el); }
}

function deepUnref(node) {
	var obody = node.body;

	for (var i = 0; i < obody.length; i++) {
		var o2 = obody[i];
		delete o2.el._node;

		if (o2.vm != null)
			{ o2.vm.node = null; }

		if (isArr(o2.body))
			{ deepUnref(o2); }
	}
}

function clearChildren(parent) {
	var parEl = parent.el;

	if ((parent.flags & DEEP_REMOVE) === 0) {
		isArr(parent.body) && deepUnref(parent);
		parEl.textContent = null;
	}
	else {
		var el = parEl.firstChild;

		do {
			var next = nextSib(el);
			removeChild(parEl, el);
		} while (el = next);
	}
}

// todo: hooks
function insertBefore(parEl, el, refEl) {
	var node = el._node, inDom = el.parentNode != null;

	// el === refEl is asserted as a no-op insert called to fire hooks
	var vm = (el === refEl || !inDom) ? node.vm : null;

	if (vm != null)
		{ fireHook(vm.hooks, "willMount", vm, vm.data); }

	fireHook(node.hooks, inDom ? "willReinsert" : "willInsert", node);
	parEl.insertBefore(el, refEl);
	fireHook(node.hooks, inDom ? "didReinsert" : "didInsert", node);

	if (vm != null)
		{ fireHook(vm.hooks, "didMount", vm, vm.data); }
}

function insertAfter(parEl, el, refEl) {
	insertBefore(parEl, el, refEl ? nextSib(refEl) : null);
}

var onemit = {};

function emitCfg(cfg) {
	assignObj(onemit, cfg);
}

function emit(evName) {
	var targ = this,
		src = targ;

	var args = sliceArgs(arguments, 1).concat(src, src.data);

	do {
		var evs = targ.onemit;
		var fn = evs ? evs[evName] : null;

		if (fn) {
			fn.apply(targ, args);
			break;
		}
	} while (targ = targ.parent());

	if (onemit[evName])
		{ onemit[evName].apply(targ, args); }
}

var onevent = noop;

function config(newCfg) {
	onevent = newCfg.onevent || onevent;

	{
		if (newCfg.onemit)
			{ emitCfg(newCfg.onemit); }
	}

	
}

function bindEv(el, type, fn) {
	el[type] = fn;
}

function exec(fn, args, e, node, vm) {
	var out = fn.apply(vm, args.concat([e, node, vm, vm.data]));

	// should these respect out === false?
	vm.onevent(e, node, vm, vm.data, args);
	onevent.call(null, e, node, vm, vm.data, args);

	if (out === false) {
		e.preventDefault();
		e.stopPropagation();
	}
}

function handle(e) {
	var node = closestVNode(e.target);
	var vm = getVm(node);

	var evDef = e.currentTarget._node.attrs["on" + e.type], fn, args;

	if (isArr(evDef)) {
		fn = evDef[0];
		args = evDef.slice(1);
		exec(fn, args, e, node, vm);
	}
	else {
		for (var sel in evDef) {
			if (e.target.matches(sel)) {
				var evDef2 = evDef[sel];

				if (isArr(evDef2)) {
					fn = evDef2[0];
					args = evDef2.slice(1);
				}
				else {
					fn = evDef2;
					args = [];
				}

				exec(fn, args, e, node, vm);
			}
		}
	}
}

function patchEvent(node, name, nval, oval) {
	if (nval === oval)
		{ return; }

	var el = node.el;

	if (nval == null || isFunc(nval))
		{ bindEv(el, name, nval); }
	else if (oval == null)
		{ bindEv(el, name, handle); }
}

function remAttr(node, name, asProp) {
	if (name[0] === ".") {
		name = name.substr(1);
		asProp = true;
	}

	if (asProp)
		{ node.el[name] = ""; }
	else
		{ node.el.removeAttribute(name); }
}

// setAttr
// diff, ".", "on*", bool vals, skip _*, value/checked/selected selectedIndex
function setAttr(node, name, val, asProp, initial) {
	var el = node.el;

	if (val == null)
		{ !initial && remAttr(node, name, false); }		// will also removeAttr of style: null
	else if (node.ns != null)
		{ el.setAttribute(name, val); }
	else if (name === "class")
		{ el.className = val; }
	else if (name === "id" || typeof val === "boolean" || asProp)
		{ el[name] = val; }
	else if (name[0] === ".")
		{ el[name.substr(1)] = val; }
	else
		{ el.setAttribute(name, val); }
}

function patchAttrs(vnode, donor, initial) {
	var nattrs = vnode.attrs || emptyObj;
	var oattrs = donor.attrs || emptyObj;

	if (nattrs === oattrs) {
		
	}
	else {
		for (var key in nattrs) {
			var nval = nattrs[key];
			var isDyn = isDynProp(vnode.tag, key);
			var oval = isDyn ? vnode.el[key] : oattrs[key];

			if (nval === oval) {}
			else if (isStyleProp(key))
				{ patchStyle(vnode, donor); }
			else if (isSplProp(key)) {}
			else if (isEvProp(key))
				{ patchEvent(vnode, key, nval, oval); }
			else
				{ setAttr(vnode, key, nval, isDyn, initial); }
		}

		// TODO: bench style.cssText = "" vs removeAttribute("style")
		for (var key in oattrs) {
			!(key in nattrs) &&
			!isSplProp(key) &&
			remAttr(vnode, key, isDynProp(vnode.tag, key) || isEvProp(key));
		}
	}
}

function createView(view, data, key, opts) {
	if (view.type === VVIEW) {
		data	= view.data;
		key		= view.key;
		opts	= view.opts;
		view	= view.view;
	}

	return new ViewModel(view, data, key, opts);
}

//import { XML_NS, XLINK_NS } from './defineSvgElement';
function hydrateBody(vnode) {
	for (var i = 0; i < vnode.body.length; i++) {
		var vnode2 = vnode.body[i];
		var type2 = vnode2.type;

		// ELEMENT,TEXT,COMMENT
		if (type2 <= COMMENT)
			{ insertBefore(vnode.el, hydrate(vnode2)); }		// vnode.el.appendChild(hydrate(vnode2))
		else if (type2 === VVIEW) {
			var vm = createView(vnode2.view, vnode2.data, vnode2.key, vnode2.opts)._redraw(vnode, i, false);		// todo: handle new data updates
			type2 = vm.node.type;
			insertBefore(vnode.el, hydrate(vm.node));
		}
		else if (type2 === VMODEL) {
			var vm = vnode2.vm;
			vm._redraw(vnode, i);					// , false
			type2 = vm.node.type;
			insertBefore(vnode.el, vm.node.el);		// , hydrate(vm.node)
		}
	}
}

//  TODO: DRY this out. reusing normal patch here negatively affects V8's JIT
function hydrate(vnode, withEl) {
	if (vnode.el == null) {
		if (vnode.type === ELEMENT) {
			vnode.el = withEl || createElement(vnode.tag, vnode.ns);

		//	if (vnode.tag === "svg")
		//		vnode.el.setAttributeNS(XML_NS, 'xmlns:xlink', XLINK_NS);

			if (vnode.attrs != null)
				{ patchAttrs(vnode, emptyObj, true); }

			if ((vnode.flags & LAZY_LIST) === LAZY_LIST)	// vnode.body instanceof LazyList
				{ vnode.body.body(vnode); }

			if (isArr(vnode.body))
				{ hydrateBody(vnode); }
			else if (vnode.body != null && vnode.body !== "")
				{ vnode.el.textContent = vnode.body; }
		}
		else if (vnode.type === TEXT)
			{ vnode.el = withEl || createTextNode(vnode.body); }
		else if (vnode.type === COMMENT)
			{ vnode.el = withEl || createComment(vnode.body); }
	}

	vnode.el._node = vnode;

	return vnode.el;
}

// prevent GCC from inlining some large funcs (which negatively affects Chrome's JIT)
//window.syncChildren = syncChildren;
window.lisMove = lisMove;

function nextNode(node, body) {
	return body[node.idx + 1];
}

function prevNode(node, body) {
	return body[node.idx - 1];
}

function parentNode(node) {
	return node.parent;
}

var BREAK = 1;
var BREAK_ALL = 2;

function syncDir(advSib, advNode, insert, sibName, nodeName, invSibName, invNodeName, invInsert) {
	return function(node, parEl, body, state, convTest, lis) {
		var sibNode, tmpSib;

		if (state[sibName] != null) {
			// skip dom elements not created by domvm
			if ((sibNode = state[sibName]._node) == null) {
				state[sibName] = advSib(state[sibName]);
				return;
			}

			if (parentNode(sibNode) !== node) {
				tmpSib = advSib(state[sibName]);
				sibNode.vm != null ? sibNode.vm.unmount(true) : removeChild(parEl, state[sibName]);
				state[sibName] = tmpSib;
				return;
			}
		}

		if (state[nodeName] == convTest)
			{ return BREAK_ALL; }
		else if (state[nodeName].el == null) {
			insert(parEl, hydrate(state[nodeName]), state[sibName]);	// should lis be updated here?
			state[nodeName] = advNode(state[nodeName], body);		// also need to advance sib?
		}
		else if (state[nodeName].el === state[sibName]) {
			state[nodeName] = advNode(state[nodeName], body);
			state[sibName] = advSib(state[sibName]);
		}
		// head->tail or tail->head
		else if (!lis && sibNode === state[invNodeName]) {
			tmpSib = state[sibName];
			state[sibName] = advSib(tmpSib);
			invInsert(parEl, tmpSib, state[invSibName]);
			state[invSibName] = tmpSib;
		}
		else {
			if (lis && state[sibName] != null)
				{ return lisMove(advSib, advNode, insert, sibName, nodeName, parEl, body, sibNode, state); }

			return BREAK;
		}
	};
}

function lisMove(advSib, advNode, insert, sibName, nodeName, parEl, body, sibNode, state) {
	if (sibNode._lis) {
		insert(parEl, state[nodeName].el, state[sibName]);
		state[nodeName] = advNode(state[nodeName], body);
	}
	else {
		// find closest tomb
		var t = binaryFindLarger(sibNode.idx, state.tombs);
		sibNode._lis = true;
		var tmpSib = advSib(state[sibName]);
		insert(parEl, state[sibName], t != null ? body[state.tombs[t]].el : t);

		if (t == null)
			{ state.tombs.push(sibNode.idx); }
		else
			{ state.tombs.splice(t, 0, sibNode.idx); }

		state[sibName] = tmpSib;
	}
}

var syncLft = syncDir(nextSib, nextNode, insertBefore, "lftSib", "lftNode", "rgtSib", "rgtNode", insertAfter);
var syncRgt = syncDir(prevSib, prevNode, insertAfter, "rgtSib", "rgtNode", "lftSib", "lftNode", insertBefore);

function syncChildren(node, donor) {
	var obody	= donor.body,
		parEl	= node.el,
		body	= node.body,
		state = {
			lftNode:	body[0],
			rgtNode:	body[body.length - 1],
			lftSib:		((obody)[0] || emptyObj).el,
			rgtSib:		(obody[obody.length - 1] || emptyObj).el,
		};

	converge:
	while (1) {
//		from_left:
		while (1) {
			var l = syncLft(node, parEl, body, state, null, false);
			if (l === BREAK) { break; }
			if (l === BREAK_ALL) { break converge; }
		}

//		from_right:
		while (1) {
			var r = syncRgt(node, parEl, body, state, state.lftNode, false);
			if (r === BREAK) { break; }
			if (r === BREAK_ALL) { break converge; }
		}

		sortDOM(node, parEl, body, state);
		break;
	}
}

// TODO: also use the state.rgtSib and state.rgtNode bounds, plus reduce LIS range
function sortDOM(node, parEl, body, state) {
	var kids = Array.prototype.slice.call(parEl.childNodes);
	var domIdxs = [];

	for (var k = 0; k < kids.length; k++) {
		var n = kids[k]._node;

		if (n.parent === node)
			{ domIdxs.push(n.idx); }
	}

	// list of non-movable vnode indices (already in correct order in old dom)
	var tombs = longestIncreasingSubsequence(domIdxs).map(function (i) { return domIdxs[i]; });

	for (var i = 0; i < tombs.length; i++)
		{ body[tombs[i]]._lis = true; }

	state.tombs = tombs;

	while (1) {
		var r = syncLft(node, parEl, body, state, null, true);
		if (r === BREAK_ALL) { break; }
	}
}

function alreadyAdopted(vnode) {
	return vnode.el._node.parent !== vnode.parent;
}

function takeSeqIndex(n, obody, fromIdx) {
	return obody[fromIdx];
}

function findSeqThorough(n, obody, fromIdx) {		// pre-tested isView?
	for (; fromIdx < obody.length; fromIdx++) {
		var o = obody[fromIdx];

		if (o.vm != null) {
			// match by key & viewFn || vm
			if (n.type === VVIEW && o.vm.view === n.view && o.vm.key === n.key || n.type === VMODEL && o.vm === n.vm)
				{ return o; }
		}
		else if (!alreadyAdopted(o) && n.tag === o.tag && n.type === o.type && n.key === o.key && (n.flags & ~DEEP_REMOVE) === (o.flags & ~DEEP_REMOVE))
			{ return o; }
	}

	return null;
}

function findHashKeyed(n, obody, fromIdx) {
	return obody[obody._keys[n.key]];
}

/*
// list must be a sorted list of vnodes by key
function findBinKeyed(n, list) {
	var idx = binaryKeySearch(list, n.key);
	return idx > -1 ? list[idx] : null;
}
*/

// have it handle initial hydrate? !donor?
// types (and tags if ELEM) are assumed the same, and donor exists
function patch(vnode, donor) {
	fireHook(donor.hooks, "willRecycle", donor, vnode);

	var el = vnode.el = donor.el;

	var obody = donor.body;
	var nbody = vnode.body;

	el._node = vnode;

	// "" => ""
	if (vnode.type === TEXT && nbody !== obody) {
		el.nodeValue = nbody;
		return;
	}

	if (vnode.attrs != null || donor.attrs != null)
		{ patchAttrs(vnode, donor, false); }

	// patch events

	var oldIsArr = isArr(obody);
	var newIsArr = isArr(nbody);
	var lazyList = (vnode.flags & LAZY_LIST) === LAZY_LIST;

//	var nonEqNewBody = nbody != null && nbody !== obody;

	if (oldIsArr) {
		// [] => []
		if (newIsArr || lazyList)
			{ patchChildren(vnode, donor); }
		// [] => "" | null
		else if (nbody !== obody) {
			if (nbody != null)
				{ el.textContent = nbody; }
			else
				{ clearChildren(donor); }
		}
	}
	else {
		// "" | null => []
		if (newIsArr) {
			clearChildren(donor);
			hydrateBody(vnode);
		}
		// "" | null => "" | null
		else if (nbody !== obody) {
			if (el.firstChild)
				{ el.firstChild.nodeValue = nbody; }
			else
				{ el.textContent = nbody; }
		}
	}

	fireHook(donor.hooks, "didRecycle", donor, vnode);
}

// larger qtys of KEYED_LIST children will use binary search
//const SEQ_FAILS_MAX = 100;

// TODO: modify vtree matcher to work similar to dom reconciler for keyed from left -> from right -> head/tail -> binary
// fall back to binary if after failing nri - nli > SEQ_FAILS_MAX
// while-advance non-keyed fromIdx
// [] => []
function patchChildren(vnode, donor) {
	var nbody		= vnode.body,
		nlen		= nbody.length,
		obody		= donor.body,
		olen		= obody.length,
		isLazy		= (vnode.flags & LAZY_LIST) === LAZY_LIST,
		isFixed		= (vnode.flags & FIXED_BODY) === FIXED_BODY,
		isKeyed		= (vnode.flags & KEYED_LIST) === KEYED_LIST,
		domSync		= !isFixed && vnode.type === ELEMENT,
		doFind		= true,
		find		= (
			isKeyed ? findHashKeyed :				// keyed lists/lazyLists
			isFixed || isLazy ? takeSeqIndex :		// unkeyed lazyLists and FIXED_BODY
			findSeqThorough							// more complex stuff
		);

	if (isKeyed) {
		var keys = {};
		for (var i = 0; i < obody.length; i++)
			{ keys[obody[i].key] = i; }
		obody._keys = keys;
	}

	if (domSync && nlen === 0) {
		clearChildren(donor);
		if (isLazy)
			{ vnode.body = []; }	// nbody.tpl(all);
		return;
	}

	var donor2,
		node2,
		foundIdx,
		patched = 0,
		everNonseq = false,
		fromIdx = 0;		// first unrecycled node (search head)

	if (isLazy) {
		var fnode2 = {key: null};
		var nbodyNew = Array(nlen);
	}

	for (var i = 0; i < nlen; i++) {
		if (isLazy) {
			var remake = false;
			var diffRes = null;

			if (doFind) {
				if (isKeyed)
					{ fnode2.key = nbody.key(i); }

				donor2 = find(fnode2, obody, fromIdx);
			}

			if (donor2 != null) {
                foundIdx = donor2.idx;
				diffRes = nbody.diff(i, donor2);

				// diff returns same, so cheaply adopt vnode without patching
				if (diffRes === true) {
					node2 = donor2;
					node2.parent = vnode;
					node2.idx = i;
					node2._lis = false;
				}
				// diff returns new diffVals, so generate new vnode & patch
				else
					{ remake = true; }
			}
			else
				{ remake = true; }

			if (remake) {
				node2 = nbody.tpl(i);			// what if this is a VVIEW, VMODEL, injected element?
				preProc(node2, vnode, i);

				node2._diff = diffRes != null ? diffRes : nbody.diff(i);

				if (donor2 != null)
					{ patch(node2, donor2); }
			}
			else {
				// TODO: flag tmp FIXED_BODY on unchanged nodes?

				// domSync = true;		if any idx changes or new nodes added/removed
			}

			nbodyNew[i] = node2;
		}
		else {
			var node2 = nbody[i];
			var type2 = node2.type;

			// ELEMENT,TEXT,COMMENT
			if (type2 <= COMMENT) {
				if (donor2 = doFind && find(node2, obody, fromIdx)) {
					patch(node2, donor2);
					foundIdx = donor2.idx;
				}
			}
			else if (type2 === VVIEW) {
				if (donor2 = doFind && find(node2, obody, fromIdx)) {		// update/moveTo
					foundIdx = donor2.idx;
					var vm = donor2.vm._update(node2.data, vnode, i);		// withDOM
				}
				else
					{ var vm = createView(node2.view, node2.data, node2.key, node2.opts)._redraw(vnode, i, false); }	// createView, no dom (will be handled by sync below)

				type2 = vm.node.type;
			}
			else if (type2 === VMODEL) {
				// if the injected vm has never been rendered, this vm._update() serves as the
				// initial vtree creator, but must avoid hydrating (creating .el) because syncChildren()
				// which is responsible for mounting below (and optionally hydrating), tests .el presence
				// to determine if hydration & mounting are needed
				var withDOM = isHydrated(node2.vm);

				var vm = node2.vm._update(node2.data, vnode, i, withDOM);
				type2 = vm.node.type;
			}
		}

		// found donor & during a sequential search ...at search head
		if (!isKeyed && donor2 != null) {
			if (foundIdx === fromIdx) {
				// advance head
				fromIdx++;
				// if all old vnodes adopted and more exist, stop searching
				if (fromIdx === olen && nlen > olen) {
					// short-circuit find, allow loop just create/init rest
					donor2 = null;
					doFind = false;
				}
			}
			else
				{ everNonseq = true; }

			if (olen > 100 && everNonseq && ++patched % 10 === 0)
				{ while (fromIdx < olen && alreadyAdopted(obody[fromIdx]))
					{ fromIdx++; } }
		}
	}

	// replace List w/ new body
	if (isLazy)
		{ vnode.body = nbodyNew; }

	domSync && syncChildren(vnode, donor);
}

// view + key serve as the vm's unique identity
function ViewModel(view, data, key, opts) {
	var vm = this;

	vm.view = view;
	vm.data = data;
	vm.key = key;

	if (opts) {
		vm.opts = opts;
		vm.config(opts);
	}

	var out = isPlainObj(view) ? view : view.call(vm, vm, data, key, opts);

	if (isFunc(out))
		{ vm.render = out; }
	else {
		vm.render = out.render;
		vm.config(out);
	}

	// these must be wrapped here since they're debounced per view
	vm._redrawAsync = raft(function (_) { return vm.redraw(true); });
	vm._updateAsync = raft(function (newData) { return vm.update(newData, true); });

	vm.init && vm.init.call(vm, vm, vm.data, vm.key, opts);
}

var ViewModelProto = ViewModel.prototype = {
	constructor: ViewModel,

	_diff:	null,	// diff cache

	init:	null,
	view:	null,
	key:	null,
	data:	null,
	state:	null,
	api:	null,
	opts:	null,
	node:	null,
	hooks:	null,
	onevent: noop,
	refs:	null,
	render:	null,

	mount: mount,
	unmount: unmount,
	config: function(opts) {
		var t = this;

		if (opts.init)
			{ t.init = opts.init; }
		if (opts.diff)
			{ t.diff = opts.diff; }
		if (opts.onevent)
			{ t.onevent = opts.onevent; }

		// maybe invert assignment order?
		if (opts.hooks)
			{ t.hooks = assignObj(t.hooks || {}, opts.hooks); }

		{
			if (opts.onemit)
				{ t.onemit = assignObj(t.onemit || {}, opts.onemit); }
		}
	},
	parent: function() {
		return getVm(this.node.parent);
	},
	root: function() {
		var p = this.node;

		while (p.parent)
			{ p = p.parent; }

		return p.vm;
	},
	redraw: function(sync) {
		var vm = this;
		sync ? vm._redraw(null, null, isHydrated(vm)) : vm._redrawAsync();
		return vm;
	},
	update: function(newData, sync) {
		var vm = this;
		sync ? vm._update(newData, null, null, isHydrated(vm)) : vm._updateAsync(newData);
		return vm;
	},

	_update: updateSync,
	_redraw: redrawSync,
	_redrawAsync: null,
	_updateAsync: null,
};

function mount(el, isRoot) {
	var vm = this;

	if (isRoot) {
		clearChildren({el: el, flags: 0});

		vm._redraw(null, null, false);

		// if placeholder node doesnt match root tag
		if (el.nodeName.toLowerCase() !== vm.node.tag) {
			hydrate(vm.node);
			insertBefore(el.parentNode, vm.node.el, el);
			el.parentNode.removeChild(el);
		}
		else
			{ insertBefore(el.parentNode, hydrate(vm.node, el), el); }
	}
	else {
		vm._redraw(null, null);

		if (el)
			{ insertBefore(el, vm.node.el); }
	}

	if (el)
		{ drainDidHooks(vm); }

	return vm;
}

// asSub means this was called from a sub-routine, so don't drain did* hook queue
function unmount(asSub) {
	var vm = this;

	var node = vm.node;
	var parEl = node.el.parentNode;

	// edge bug: this could also be willRemove promise-delayed; should .then() or something to make sure hooks fire in order
	removeChild(parEl, node.el);

	if (!asSub)
		{ drainDidHooks(vm); }
}

function reParent(vm, vold, newParent, newIdx) {
	if (newParent != null) {
		newParent.body[newIdx] = vold;
		vold.idx = newIdx;
		vold.parent = newParent;
		vold._lis = false;
	}
	return vm;
}

function redrawSync(newParent, newIdx, withDOM) {
	var isRedrawRoot = newParent == null;
	var vm = this;
	var isMounted = vm.node && vm.node.el && vm.node.el.parentNode;

	var vold = vm.node, oldDiff, newDiff;

	if (vm.diff != null) {
		oldDiff = vm._diff;
		vm._diff = newDiff = vm.diff(vm, vm.data);

		if (vold != null) {
			var cmpFn = isArr(oldDiff) ? cmpArr : cmpObj;
			var isSame = oldDiff === newDiff || cmpFn(oldDiff, newDiff);

			if (isSame)
				{ return reParent(vm, vold, newParent, newIdx); }
		}
	}

	isMounted && fireHook(vm.hooks, "willRedraw", vm, vm.data);

	var vnew = vm.render.call(vm, vm, vm.data, oldDiff, newDiff);

	if (vnew === vold)
		{ return reParent(vm, vold, newParent, newIdx); }

	// todo: test result of willRedraw hooks before clearing refs
	vm.refs = null;

	// always assign vm key to root vnode (this is a de-opt)
	if (vm.key != null && vnew.key !== vm.key)
		{ vnew.key = vm.key; }

	vm.node = vnew;

	if (newParent) {
		preProc(vnew, newParent, newIdx, vm);
		newParent.body[newIdx] = vnew;
	}
	else if (vold && vold.parent) {
		preProc(vnew, vold.parent, vold.idx, vm);
		vold.parent.body[vold.idx] = vnew;
	}
	else
		{ preProc(vnew, null, null, vm); }

	if (withDOM !== false) {
		if (vold) {
			// root node replacement
			if (vold.tag !== vnew.tag || vold.key !== vnew.key) {
				// hack to prevent the replacement from triggering mount/unmount
				vold.vm = vnew.vm = null;

				var parEl = vold.el.parentNode;
				var refEl = nextSib(vold.el);
				removeChild(parEl, vold.el);
				insertBefore(parEl, hydrate(vnew), refEl);

				// another hack that allows any higher-level syncChildren to set
				// reconciliation bounds using a live node
				vold.el = vnew.el;

				// restore
				vnew.vm = vm;
			}
			else
				{ patch(vnew, vold); }
		}
		else
			{ hydrate(vnew); }
	}

	isMounted && fireHook(vm.hooks, "didRedraw", vm, vm.data);

	if (isRedrawRoot && isMounted)
		{ drainDidHooks(vm); }

	return vm;
}

// this also doubles as moveTo
// TODO? @withRedraw (prevent redraw from firing)
function updateSync(newData, newParent, newIdx, withDOM) {
	var vm = this;

	if (newData != null) {
		if (vm.data !== newData) {
			fireHook(vm.hooks, "willUpdate", vm, newData);
			vm.data = newData;

			
		}
	}

	return vm._redraw(newParent, newIdx, withDOM);
}

function defineElement(tag, arg1, arg2, flags) {
	var attrs, body;

	if (arg2 == null) {
		if (isPlainObj(arg1))
			{ attrs = arg1; }
		else
			{ body = arg1; }
	}
	else {
		attrs = arg1;
		body = arg2;
	}

	return initElementNode(tag, attrs, body, flags);
}

//export const XML_NS = "http://www.w3.org/2000/xmlns/";
var SVG_NS = "http://www.w3.org/2000/svg";

function defineSvgElement(tag, arg1, arg2, flags) {
	var n = defineElement(tag, arg1, arg2, flags);
	n.ns = SVG_NS;
	return n;
}

function defineComment(body) {
	var node = new VNode;
	node.type = COMMENT;
	node.body = body;
	return node;
}

// placeholder for declared views
function VView(view, data, key, opts) {
	this.view = view;
	this.data = data;
	this.key = key;
	this.opts = opts;
}

VView.prototype = {
	constructor: VView,

	type: VVIEW,
	view: null,
	data: null,
	key: null,
	opts: null,
};

function defineView(view, data, key, opts) {
	return new VView(view, data, key, opts);
}

// placeholder for injected ViewModels
function VModel(vm) {
	this.vm = vm;
}

VModel.prototype = {
	constructor: VModel,

	type: VMODEL,
	vm: null,
};

function injectView(vm) {
//	if (vm.node == null)
//		vm._redraw(null, null, false);

//	return vm.node;

	return new VModel(vm);
}

function injectElement(el) {
	var node = new VNode;
	node.type = ELEMENT;
	node.el = node.key = el;
	return node;
}

function lazyList(items, cfg) {
	var len = items.length;

	var self = {
		items: items,
		length: len,
		// defaults to returning item identity (or position?)
		key: function(i) {
			return cfg.key(items[i], i);
		},
		// default returns 0?
		diff: function(i, donor) {
			var newVals = cfg.diff(items[i], i);
			if (donor == null)
				{ return newVals; }
			var oldVals = donor._diff;
			var same = newVals === oldVals || isArr(oldVals) ? cmpArr(newVals, oldVals) : cmpObj(newVals, oldVals);
			return same || newVals;
		},
		tpl: function(i) {
			return cfg.tpl(items[i], i);
		},
		map: function(tpl) {
			cfg.tpl = tpl;
			return self;
		},
		body: function(vnode) {
			var nbody = Array(len);

			for (var i = 0; i < len; i++) {
				var vnode2 = self.tpl(i);

			//	if ((vnode.flags & KEYED_LIST) === KEYED_LIST && self. != null)
			//		vnode2.key = getKey(item);

				vnode2._diff = self.diff(i);			// holds oldVals for cmp

				nbody[i] = vnode2;

				// run preproc pass (should this be just preProc in above loop?) bench
				preProc(vnode2, vnode, i);
			}

			// replace List with generated body
			vnode.body = nbody;
		}
	};

	return self;
}

var nano = {
	config: config,

	ViewModel: ViewModel,
	VNode: VNode,

	createView: createView,

	defineElement: defineElement,
	defineSvgElement: defineSvgElement,
	defineText: defineText,
	defineComment: defineComment,
	defineView: defineView,

	injectView: injectView,
	injectElement: injectElement,

	lazyList: lazyList,

	FIXED_BODY: FIXED_BODY,
	DEEP_REMOVE: DEEP_REMOVE,
	KEYED_LIST: KEYED_LIST,
	LAZY_LIST: LAZY_LIST,
};

function protoPatch(n, doRepaint) {
	patch$1(this, n, doRepaint);
}

// newNode can be either {class: style: } or full new VNode
// will/didPatch hooks?
function patch$1(o, n, doRepaint) {
	if (n.type != null) {
		// no full patching of view roots, just use redraw!
		if (o.vm != null)
			{ return; }

		preProc(n, o.parent, o.idx, null);
		o.parent.body[o.idx] = n;
		patch(n, o);
		doRepaint && repaint(n);
		drainDidHooks(getVm(n));
	}
	else {
		// TODO: re-establish refs

		// shallow-clone target
		var donor = Object.create(o);
		// fixate orig attrs
		donor.attrs = assignObj({}, o.attrs);
		// assign new attrs into live targ node
		var oattrs = assignObj(o.attrs, n);
		// prepend any fixed shorthand class
		if (o._class != null) {
			var aclass = oattrs.class;
			oattrs.class = aclass != null && aclass !== "" ? o._class + " " + aclass : o._class;
		}

		patchAttrs(o, donor);

		doRepaint && repaint(o);
	}
}

VNodeProto.patch = protoPatch;

function nextSubVms(n, accum) {
	var body = n.body;

	if (isArr(body)) {
		for (var i = 0; i < body.length; i++) {
			var n2 = body[i];

			if (n2.vm != null)
				{ accum.push(n2.vm); }
			else
				{ nextSubVms(n2, accum); }
		}
	}

	return accum;
}

function defineElementSpread(tag) {
	var args = arguments;
	var len = args.length;
	var body, attrs;

	if (len > 1) {
		var bodyIdx = 1;

		if (isPlainObj(args[1])) {
			attrs = args[1];
			bodyIdx = 2;
		}

		if (len === bodyIdx + 1 && (isVal(args[bodyIdx]) || isArr(args[bodyIdx]) || attrs && (attrs._flags & LAZY_LIST) === LAZY_LIST))
			{ body = args[bodyIdx]; }
		else
			{ body = sliceArgs(args, bodyIdx); }
	}

	return initElementNode(tag, attrs, body);
}

function defineSvgElementSpread() {
	var n = defineElementSpread.apply(null, arguments);
	n.ns = SVG_NS;
	return n;
}

ViewModelProto.emit = emit;
ViewModelProto.onemit = null;

ViewModelProto.body = function() {
	return nextSubVms(this.node, []);
};

nano.defineElementSpread = defineElementSpread;
nano.defineSvgElementSpread = defineSvgElementSpread;

return nano;

})));
//# sourceMappingURL=domvm.micro.js.map


/***/ }),
/* 47 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(Promise) {
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Loader = void 0;
var core_1 = __webpack_require__(0);
var helpers_1 = __webpack_require__(3);
var types_1 = __webpack_require__(5);
var core_2 = __webpack_require__(0);
var Loader = /** @class */ (function () {
    function Loader(parent, changes) {
        this._parent = parent;
        this._changes = changes; // todo: [dirty] mutation
    }
    Loader.prototype.load = function (url, driver) {
        var _this = this;
        // TODO: change way for checking lazyLoad
        if (url.config && !this._parent.events.fire(types_1.DataEvents.beforeLazyLoad, [])) {
            return;
        }
        return (this._parent.loadData = url
            .load()
            .then(function (data) {
            if (data) {
                return _this.parse(data, driver);
            }
            else {
                return [];
            }
        })
            .catch(function (error) {
            _this._parent.events.fire(types_1.DataEvents.loadError, [error]);
        }));
    };
    Loader.prototype.parse = function (data, driver) {
        var _this = this;
        if (driver === void 0) { driver = types_1.DataDriver.json; }
        if (driver === "json" && !(0, helpers_1.hasJsonOrArrayStructure)(data)) {
            this._parent.events.fire(types_1.DataEvents.loadError, ["Uncaught SyntaxError: Unexpected end of input"]);
        }
        driver = (0, helpers_1.toDataDriver)(driver);
        data = driver.toJsonArray(data);
        if (!(data instanceof Array)) {
            var totalCount = data.total_count - 1;
            var from_1 = data.from;
            data = data.data;
            if (this._parent.getLength() === 0) {
                var newData = [];
                for (var i = 0, j = 0; i <= totalCount; i++) {
                    if (i >= from_1 && i <= from_1 + data.length - 1) {
                        newData.push(data[j]);
                        j++;
                    }
                    else {
                        newData.push({ $empty: true });
                    }
                }
                data = newData;
            }
            else {
                data.forEach(function (newItem, i) {
                    var index = from_1 + i;
                    var oldId = _this._parent.getId(index);
                    if ((0, core_1.isId)(oldId)) {
                        var emptyItem = _this._parent.getItem(oldId);
                        if (emptyItem && emptyItem.$empty) {
                            var id = newItem.id || emptyItem.id;
                            if ((0, core_2.isDefined)(newItem.id)) {
                                _this._parent.changeId(oldId, id, true);
                            }
                            _this._parent.update(id, __assign(__assign({}, newItem), { $empty: undefined }), true);
                        }
                    }
                    else {
                        (0, helpers_1.dhxWarning)("item not found");
                    }
                });
                this._parent.events.fire(types_1.DataEvents.afterLazyLoad, [from_1, data.length]);
                this._parent.events.fire(types_1.DataEvents.change);
                return data;
            }
        }
        if (this._parent.getInitialData()) {
            this._parent.removeAll();
        }
        this._parent.$parse(data);
        return data;
    };
    Loader.prototype.save = function (url) {
        var _this = this;
        var uniqueChanges = this._getUniqueOrder();
        var _loop_1 = function (el) {
            if (el.saving || el.pending) {
                (0, helpers_1.dhxWarning)("item is saving");
            }
            else {
                var prevEl_1 = this_1._findPrevState(el.id);
                if (prevEl_1 && prevEl_1.saving) {
                    var pending = new Promise(function (res, rej) {
                        prevEl_1.promise
                            .then(function () {
                            el.pending = false;
                            res(_this._setPromise(el, url));
                        })
                            .catch(function (err) {
                            _this._removeFromOrder(prevEl_1);
                            _this._setPromise(el, url);
                            (0, helpers_1.dhxWarning)(err);
                            rej(err);
                        });
                    });
                    this_1._addToChain(pending);
                    el.pending = true;
                }
                else {
                    this_1._setPromise(el, url);
                }
            }
        };
        var this_1 = this;
        for (var _i = 0, uniqueChanges_1 = uniqueChanges; _i < uniqueChanges_1.length; _i++) {
            var el = uniqueChanges_1[_i];
            _loop_1(el);
        }
        if (uniqueChanges.length) {
            this._parent.saveData.then(function () {
                _this._saving = false;
            });
        }
    };
    Loader.prototype.updateChanges = function (changes) {
        this._changes = changes;
    };
    Loader.prototype._setPromise = function (el, url) {
        var _this = this;
        var status;
        switch (el.status) {
            case "remove":
                status = "delete";
                break;
            case "add":
                status = "insert";
                break;
            default:
                status = el.status;
                break;
        }
        el.promise = url.save(el.obj, status);
        el.promise
            .then(function () {
            _this._removeFromOrder(el);
        })
            .catch(function (err) {
            el.saving = false;
            el.error = true;
            (0, helpers_1.dhxError)(err);
        });
        el.saving = true;
        this._saving = true;
        this._addToChain(el.promise);
        return el.promise;
    };
    Loader.prototype._addToChain = function (promise) {
        // eslint-disable-next-line @typescript-eslint/no-misused-promises
        if (this._parent.saveData && this._saving) {
            this._parent.saveData = this._parent.saveData.then(function () { return promise; });
        }
        else {
            this._parent.saveData = promise;
        }
    };
    Loader.prototype._findPrevState = function (id) {
        for (var _i = 0, _a = this._changes.order; _i < _a.length; _i++) {
            var el = _a[_i];
            if (el.id === id) {
                return el;
            }
        }
        return null;
    };
    Loader.prototype._removeFromOrder = function (el) {
        this._changes.order = this._changes.order.filter(function (item) { return !(0, helpers_1.isEqualObj)(item, el); });
    };
    Loader.prototype._getUniqueOrder = function () {
        return this._changes.order.reduce(function (unique, el) {
            var ind = unique.findIndex(function (item) { return item.id === el.id; });
            var involvedElem = ind > -1 ? unique[ind] : null;
            if (involvedElem && involvedElem.saving === false && involvedElem.status === "add") {
                if (el.status === "remove") {
                    unique.splice(ind, 1);
                }
                else {
                    involvedElem.obj = el.obj;
                }
            }
            else if (involvedElem && involvedElem.saving === false && involvedElem.status === "update") {
                unique.splice(ind, 1, el);
            }
            else {
                unique.push(el);
            }
            return unique;
        }, []);
    };
    return Loader;
}());
exports.Loader = Loader;

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(6)))

/***/ }),
/* 48 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.jsonToXML = void 0;
var INDENT_STEP = 4;
function ws(count) {
    return " ".repeat(count);
}
function itemToXML(item, indent) {
    if (indent === void 0) { indent = INDENT_STEP; }
    var result = ws(indent) + "<item>\n";
    for (var key in item) {
        if (Array.isArray(item[key])) {
            result += ws(indent + INDENT_STEP) + "<".concat(key, ">\n");
            result +=
                item[key].map(function (subItem) { return itemToXML(subItem, indent + INDENT_STEP * 2); }).join("\n") +
                    "\n";
            result += ws(indent + INDENT_STEP) + "</".concat(key, ">\n");
        }
        else {
            result += ws(indent + INDENT_STEP) + "<".concat(key, ">").concat(item[key], "</").concat(key, ">\n");
        }
    }
    result += ws(indent) + "</item>";
    return result;
}
function jsonToXML(data, root) {
    if (root === void 0) { root = "root"; }
    var result = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<".concat(root, ">");
    for (var i = 0; i < data.length; i++) {
        result += "\n" + itemToXML(data[i]);
    }
    return result + "\n</".concat(root, ">");
}
exports.jsonToXML = jsonToXML;


/***/ }),
/* 49 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.Sort = void 0;
var helpers_1 = __webpack_require__(3);
var Sort = /** @class */ (function () {
    function Sort() {
    }
    Sort.prototype.sort = function (array, by, perm) {
        this._createSorter(by);
        if (perm === by)
            by = null;
        if (perm || by)
            this._sort(array, perm, by);
    };
    Sort.prototype._createSorter = function (by) {
        var _this = this;
        if (by && !by.rule) {
            by.rule = function (a, b) {
                var _a, _b;
                var aa = (_a = _this._checkVal(by.as, a[by.by])) !== null && _a !== void 0 ? _a : "";
                var bb = (_b = _this._checkVal(by.as, b[by.by])) !== null && _b !== void 0 ? _b : "";
                // [TODO] why we need naturalCompare
                return (0, helpers_1.naturalCompare)(aa.toString(), bb.toString());
            };
        }
    };
    Sort.prototype._checkVal = function (method, val) {
        return method ? method.call(this, val) : val;
    };
    Sort.prototype._sort = function (arr, conf, conf2) {
        var _this = this;
        var dir = {
            asc: 1,
            desc: -1,
        };
        var sorted = arr.sort(function (a, b) {
            var t = 0;
            if (conf)
                t = conf.rule.call(_this, a, b) * (dir[conf.dir] || dir.asc);
            if (t === 0 && conf2)
                t = conf2.rule.call(_this, a, b) * (dir[conf2.dir] || dir.asc);
            return t;
        });
        var summaryIndex = sorted.findIndex(function (i) { return i.$groupSummary; });
        if (summaryIndex !== -1) {
            var summary = sorted[summaryIndex];
            sorted.splice(summaryIndex, 1);
            switch (summary.$groupSummary) {
                case "top":
                    sorted.unshift(summary);
                    break;
                case "bottom":
                    sorted.push(summary);
            }
        }
        return sorted;
    };
    return Sort;
}());
exports.Sort = Sort;


/***/ }),
/* 50 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Group = void 0;
var core_1 = __webpack_require__(0);
var methods_1 = __webpack_require__(25);
var Group = /** @class */ (function () {
    function Group() {
        this._init = [];
        this._groupSet = new Set();
    }
    Group.prototype.group = function (order, arr, config) {
        if (config === void 0) { config = {}; }
        if (!(order === null || order === void 0 ? void 0 : order.length) || !(arr === null || arr === void 0 ? void 0 : arr.length) || !Array.isArray(order)) {
            return arr || [];
        }
        this._init = __spreadArray([], arr, true);
        this._groupSet.clear();
        this._config = this.getGroupConfig(config);
        return this._group(order, arr);
    };
    Group.prototype.ungroup = function (modifiedData) {
        var data = this._init;
        this._init = [];
        this._groupSet.clear();
        if (modifiedData) {
            var pull_1 = {};
            var _loop_1 = function (index) {
                var i = modifiedData[index];
                if (i.$group)
                    return "continue";
                Object.keys(i).forEach(function (key) {
                    if (key.startsWith("$"))
                        delete i[key];
                });
                delete i.parent;
                pull_1[i.id] = i;
            };
            for (var index = 0; index < modifiedData.length; index++) {
                _loop_1(index);
            }
            return data.map(function (i) { return (__assign(__assign({}, i), pull_1[i.id])); });
        }
        return data || [];
    };
    Group.prototype.isGrouped = function () {
        return !!this._init.length;
    };
    Group.prototype.getGroupedFields = function () {
        return Array.from(this._groupSet);
    };
    Group.prototype.getGroupConfig = function (config) {
        var _a;
        if (config) {
            return {
                displayMode: config.displayMode || "column",
                field: config.field || "group",
                showMissed: (_a = config.showMissed) !== null && _a !== void 0 ? _a : true,
            };
        }
        return this._config;
    };
    Group.prototype._group = function (order, arr, parentId, level) {
        var _this = this;
        if (level === void 0) { level = 0; }
        if (level >= order.length) {
            return;
        }
        var flatTree = [];
        var grouped = {};
        var missed = [];
        var missedGroupField = "";
        var config = {};
        for (var index = 0; index < arr.length; index++) {
            var item = arr[index];
            var current = order[level];
            var isFunc = typeof current === "function" || typeof (current === null || current === void 0 ? void 0 : current.by) === "function";
            config = this._getOrderConfig(current, item);
            var ownProperty = item[config.by];
            var groupKey = isFunc ? config.by : ownProperty;
            if (!isFunc && !ownProperty && ownProperty !== 0) {
                if (this._config.showMissed) {
                    delete item.parent;
                    missed.push(item);
                }
                if (typeof this._config.showMissed === "string") {
                    missedGroupField = this._config.showMissed;
                }
                continue;
            }
            this._groupSet.add(config.by);
            if (!grouped[groupKey]) {
                grouped[groupKey] = [];
            }
            grouped[groupKey].push(item);
        }
        var groupedFields = Object.keys(grouped);
        if (missedGroupField) {
            groupedFields.push(missedGroupField);
        }
        groupedFields.forEach(function (groupKey) {
            var _a;
            var groupItems = grouped[groupKey] || missed;
            var nodeId = parentId ? "".concat(parentId, ":").concat(groupKey) : "$".concat((0, core_1.uid)(), ":").concat(groupKey);
            var aggregate = {};
            if (config.map) {
                Object.keys(config.map).forEach(function (field) {
                    aggregate[field] = _this._toAggregate(groupItems, config.map[field]);
                });
            }
            var groupNode = __assign((_a = { id: nodeId, $group: true, $row: _this._config.displayMode === "row", $count: groupItems.length }, _a[config.by] = groupKey, _a), aggregate);
            if (_this._config.displayMode === "column") {
                groupNode[_this._config.field] = groupKey;
            }
            if (parentId) {
                groupNode.parent = parentId;
            }
            flatTree.push(groupNode);
            if (level < order.length - 1) {
                flatTree.push.apply(flatTree, _this._group(order, groupItems, nodeId, level + 1));
            }
            if (level === order.length - 1) {
                groupItems.forEach(function (item) {
                    flatTree.push(__assign(__assign({}, item), { parent: nodeId }));
                });
            }
            if (config.summary) {
                _this._addSummaryRow(flatTree, nodeId, config.summary, aggregate);
            }
        });
        if (!missedGroupField)
            flatTree.push.apply(flatTree, missed);
        return flatTree;
    };
    Group.prototype._addSummaryRow = function (flatTree, groupKey, position, aggregate) {
        var summaryRow = __assign({ id: "".concat(groupKey, ":summary"), parent: groupKey, $groupSummary: position }, aggregate);
        if (position === "top") {
            var groupIndex = flatTree.findIndex(function (item) { return item.id === groupKey; });
            flatTree.splice(groupIndex + 1, 0, summaryRow);
        }
        else {
            flatTree.push(summaryRow);
        }
    };
    Group.prototype._toAggregate = function (items, order) {
        if (typeof order === "function") {
            return order(items);
        }
        var field = order[0], type = order[1];
        if (methods_1.methods[type]) {
            return methods_1.methods[type](items, field);
        }
        return "";
    };
    Group.prototype._getOrderConfig = function (order, item) {
        var by = (typeof order === "string" && order) ||
            (typeof order.by === "string" && order.by) ||
            (typeof order === "function" && order(item)) ||
            (typeof order.by === "function" && order.by(item));
        var config = { by: by };
        if (order.map) {
            config.map = order.map;
        }
        if (order.summary) {
            config.summary = order.summary;
        }
        return config;
    };
    return Group;
}());
exports.Group = Group;


/***/ }),
/* 51 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.TreeCollection = exports.addToOrder = void 0;
var core_1 = __webpack_require__(0);
var datacollection_1 = __webpack_require__(21);
var dataproxy_1 = __webpack_require__(8);
var helpers_1 = __webpack_require__(3);
var types_1 = __webpack_require__(5);
function addToOrder(store, obj, parent, index) {
    if (index !== undefined && index !== -1 && store[parent] && store[parent][index]) {
        store[parent].splice(index, 0, obj);
    }
    else {
        if (!store[parent]) {
            store[parent] = [];
        }
        store[parent].push(obj);
    }
}
exports.addToOrder = addToOrder;
var TreeCollection = /** @class */ (function (_super) {
    __extends(TreeCollection, _super);
    function TreeCollection(config, events) {
        var _a;
        var _this = _super.call(this, config, events) || this;
        _this._childs = {};
        var root = (_this._root = (config && config.rootId) || "_ROOT_" + (0, core_1.uid)());
        _this._childs = (_a = {}, _a[root] = [], _a);
        _this._initChilds = null;
        return _this;
    }
    TreeCollection.prototype.add = function (newItem, index, parent) {
        var _this = this;
        if (index === void 0) { index = -1; }
        if (parent === void 0) { parent = this._root; }
        if (!this.events.fire(types_1.DataEvents.beforeAdd, [newItem])) {
            return;
        }
        if (typeof newItem !== "object") {
            newItem = {
                value: newItem,
            };
        }
        var out;
        if (Array.isArray(newItem)) {
            out = newItem.map(function (element, key) {
                return _this._add(element, index, parent, key);
            });
        }
        else {
            out = this._add(newItem, index, parent);
        }
        this._reapplyFilters();
        return out;
    };
    TreeCollection.prototype.getRoot = function () {
        return this._root;
    };
    TreeCollection.prototype.getParent = function (id, asObj) {
        if (asObj === void 0) { asObj = false; }
        if (!this._pull[id]) {
            return null;
        }
        var parent = this._pull[id].parent;
        return asObj ? this._pull[parent] : parent;
    };
    TreeCollection.prototype.getItems = function (id) {
        if (this._childs && this._childs[id]) {
            return this._childs[id];
        }
        return [];
    };
    TreeCollection.prototype.getLength = function (id) {
        if (id === void 0) { id = this._root; }
        if (!this._childs[id]) {
            return null;
        }
        return this._childs[id].length;
    };
    TreeCollection.prototype.removeAll = function (id) {
        var _a;
        if (!(0, core_1.isId)(id)) {
            _super.prototype.removeAll.call(this);
            var root = this._root;
            this._initChilds = null;
            this._childs = (_a = {}, _a[root] = [], _a);
        }
        else if (this._childs[id]) {
            var childs = __spreadArray([], this._childs[id], true);
            for (var _i = 0, childs_1 = childs; _i < childs_1.length; _i++) {
                var child = childs_1[_i];
                this.remove(child.id);
            }
        }
    };
    TreeCollection.prototype.update = function (id, newItem, silent) {
        var parent = newItem.parent;
        if ((0, core_1.isDefined)(parent) && !this.exists(parent) && parent !== this._root) {
            (0, helpers_1.dhxWarning)("Item parent doesn't exist");
            return;
        }
        _super.prototype.update.call(this, id, newItem, silent);
    };
    TreeCollection.prototype.getIndex = function (id) {
        var parent = this.getParent(id);
        if (!parent || !this._childs[parent]) {
            return -1;
        }
        return this._childs[parent].findIndex(function (i) { return (i === null || i === void 0 ? void 0 : i.id) == id; });
    };
    TreeCollection.prototype.sort = function (rule, config, ignore) {
        var _this = this;
        var _a, _b;
        if (ignore === void 0) { ignore = false; }
        if (config === null || config === void 0 ? void 0 : config.smartSorting) {
            this._sorter = rule;
        }
        if (!ignore &&
            (!this._sortingStates.length ||
                (config === null || config === void 0 ? void 0 : config.smartSorting) ||
                (!((_a = this._sortingStates[0]) === null || _a === void 0 ? void 0 : _a.smartSorting) && !(config === null || config === void 0 ? void 0 : config.smartSorting)))) {
            this._sortingStates = [__assign(__assign({}, rule), config)];
        }
        if (rule) {
            if (!ignore) {
                this._initSortOrder = this._initSortOrder || __spreadArray([], (this._initFilterOrder || this._order), true);
                if (!(config === null || config === void 0 ? void 0 : config.smartSorting) && ((_b = this._sortingStates[0]) === null || _b === void 0 ? void 0 : _b.smartSorting)) {
                    var sortIndex = this._sortingStates.findIndex(function (i) { return i.by == rule.by; });
                    if (sortIndex !== -1) {
                        this._sortingStates[sortIndex].dir = rule.dir;
                        this._sortingStates.forEach(function (sort, index) {
                            _this.sort(sort, { smartSorting: !index }, true);
                        });
                    }
                    else {
                        this._sortingStates.push(rule);
                    }
                }
            }
            this._applySorters(rule);
        }
        else if (this._initSortOrder) {
            this._childs = {};
            this._order = [];
            this._sortingStates = [];
            this._initSortOrder.forEach(function (item) { return _this._parseItem(item); });
            this._sorter = this._initSortOrder = this._initChilds = null;
            if (this._initFilterOrder) {
                this._initFilterOrder = null;
                this.filter(null, { $restore: true }, true);
            }
        }
        if (!ignore) {
            this._reapplyFilters();
            this.events.fire(types_1.DataEvents.change, [undefined, "sort", rule]);
        }
    };
    TreeCollection.prototype.filter = function (rule, config, silent) {
        if (config === null || config === void 0 ? void 0 : config.$restore) {
            rule = this._normalizeFilters(rule || this._filters);
        }
        if (!rule || !(config === null || config === void 0 ? void 0 : config.add)) {
            if (this._initChilds) {
                this._childs = this._initChilds;
                this._initChilds = null;
            }
            if (!(config === null || config === void 0 ? void 0 : config.$restore)) {
                for (var key in this._filters) {
                    var _a = this._filters[key], rule_1 = _a.rule, conf = _a.config;
                    if (conf === null || conf === void 0 ? void 0 : conf.permanent) {
                        this._applyFilter(rule_1, conf);
                    }
                    else {
                        delete this._filters[key];
                    }
                }
            }
        }
        var id;
        if (rule && !(config === null || config === void 0 ? void 0 : config.$restore)) {
            id = (config === null || config === void 0 ? void 0 : config.id) || (0, core_1.uid)();
            if (!config)
                config = {};
            config.type = config.type || types_1.TreeFilterType.all;
            this._filters[id] = { rule: rule, config: config };
            this._applyFilter(rule, config);
        }
        else {
            for (var key in rule) {
                this._applyFilter(rule[key], this._filters[key].config);
            }
        }
        if (!silent) {
            var filters = this._getPureFilters(this._filters);
            this.events.fire(types_1.DataEvents.filter, [(0, core_1.isEmptyObj)(filters) ? null : filters]);
        }
        return id;
    };
    TreeCollection.prototype.restoreOrder = function () {
        this.resetFilter({ permanent: true }, true);
        this.sort();
    };
    TreeCollection.prototype.copy = function (id, index, target, targetId) {
        var _this = this;
        if (target === void 0) { target = this; }
        if (targetId === void 0) { targetId = this._root; }
        if (id instanceof Array) {
            return id.map(function (elementId, key) {
                return _this._copy(elementId, index, target, targetId, key);
            });
        }
        else {
            return this._copy(id, index, target, targetId);
        }
    };
    TreeCollection.prototype.move = function (id, index, target, targetId) {
        var _this = this;
        var _a;
        if (targetId === void 0) { targetId = this._root; }
        target = target || this;
        if (id instanceof Array) {
            var movedIds_1 = [];
            id.forEach(function (elementId, key) {
                if ((0, core_1.isId)(_this._move(elementId, index, target, targetId, key))) {
                    movedIds_1.push(elementId);
                }
                else {
                    (0, helpers_1.throwMoveWarning)(elementId, _this.exists(elementId));
                }
            });
            return movedIds_1;
        }
        else {
            return (_a = this._move(id, index, target, targetId)) !== null && _a !== void 0 ? _a : (0, helpers_1.throwMoveWarning)(id, this.exists(id));
        }
    };
    TreeCollection.prototype.forEach = function (callback, parent, level) {
        if (parent === void 0) { parent = this._root; }
        if (level === void 0) { level = Infinity; }
        if (!this.haveItems(parent) || level < 1) {
            return;
        }
        var array = this._childs[parent];
        for (var i = 0; i < array.length; i++) {
            callback.call(this, array[i], i, array);
            if (this.haveItems(array[i].id)) {
                this.forEach(callback, array[i].id, --level);
            }
        }
    };
    TreeCollection.prototype.eachChild = function (id, callback, direct, checkItem) {
        if (direct === void 0) { direct = true; }
        if (checkItem === void 0) { checkItem = function () { return true; }; }
        if (!this.haveItems(id)) {
            return;
        }
        for (var i = 0; i < this._childs[id].length; i++) {
            callback.call(this, this._childs[id][i], i);
            if (direct && checkItem(this._childs[id][i])) {
                this.eachChild(this._childs[id][i].id, callback, direct, checkItem);
            }
        }
    };
    TreeCollection.prototype.getNearId = function (id) {
        return id; // for selection
    };
    TreeCollection.prototype.loadItems = function (id, driver) {
        var _this = this;
        if (driver === void 0) { driver = types_1.DataDriver.json; }
        if (!this.events.fire(types_1.DataEvents.beforeItemLoad, [id])) {
            return;
        }
        var urlPart = this.config.autoload.toString();
        var url = urlPart + (urlPart.includes("?") ? "&id=".concat(id) : "?id=".concat(id));
        var proxy = new dataproxy_1.DataProxy(url);
        proxy
            .load()
            .then(function (data) {
            driver = (0, helpers_1.toDataDriver)(driver);
            data = driver.toJsonArray(data);
            _this._parse_data(data, id);
            _this.events.fire(types_1.DataEvents.change);
            _this.events.fire(types_1.DataEvents.afterItemLoad, [id]);
        })
            .catch(function (error) {
            _this.events.fire(types_1.DataEvents.loadError, [error]);
        });
    };
    TreeCollection.prototype.refreshItems = function (id, driver) {
        if (driver === void 0) { driver = types_1.DataDriver.json; }
        this.removeAll(id);
        this.loadItems(id, driver);
    };
    TreeCollection.prototype.eachParent = function (id, callback, self) {
        if (self === void 0) { self = false; }
        var item = this.getItem(id);
        if (!item) {
            return;
        }
        if (self) {
            callback.call(this, item);
        }
        if (item.parent === this._root) {
            return;
        }
        var parent = this.getItem(item.parent);
        callback.call(this, parent);
        this.eachParent(item.parent, callback);
    };
    TreeCollection.prototype.haveItems = function (id) {
        return id in this._childs;
    };
    TreeCollection.prototype.canCopy = function (id, target) {
        if (id === target) {
            return false;
        }
        var canCopy = true;
        this.eachParent(target, function (item) { return (item.id === id ? (canCopy = false) : null); }); // locate return string
        return canCopy;
    };
    TreeCollection.prototype.serialize = function (driver, checkItem) {
        if (driver === void 0) { driver = types_1.DataDriver.json; }
        var data = this._serialize(this._root, checkItem);
        var dataDriver = (0, helpers_1.toDataDriver)(driver);
        if (dataDriver) {
            return dataDriver.serialize(data);
        }
    };
    TreeCollection.prototype.getId = function (index, parent) {
        if (parent === void 0) { parent = this._root; }
        if (!this._childs[parent] || !this._childs[parent][index]) {
            return;
        }
        return this._childs[parent][index].id;
    };
    // Non public API from suite_6.4
    TreeCollection.prototype.map = function (callback, parent, direct) {
        if (parent === void 0) { parent = this._root; }
        if (direct === void 0) { direct = true; }
        var result = [];
        if (!this.haveItems(parent)) {
            return result;
        }
        for (var i = 0; i < this._childs[parent].length; i++) {
            result.push(callback.call(this, this._childs[parent][i], i, this._childs));
            if (direct) {
                var childResult = this.map(callback, this._childs[parent][i].id, direct);
                result = result.concat(childResult);
            }
        }
        return result;
    };
    TreeCollection.prototype.getRawData = function (from, to, order, mode, parent) {
        parent = parent !== null && parent !== void 0 ? parent : this._root;
        var out;
        if (!this._childs[parent])
            return [];
        if (parent === this._root)
            out = _super.prototype.getRawData.call(this, from, to, this._childs[parent]);
        else
            out = this._childs[parent];
        if (mode === 2) {
            return this.flatten(out);
        }
        return out;
    };
    TreeCollection.prototype.flatten = function (input) {
        var _this = this;
        var out = [];
        input.forEach(function (a) {
            out.push(a);
            var kids = _this._childs[a.id];
            if (kids && a.$opened) {
                out = out.concat(_this.flatten(kids));
            }
        });
        return out;
    };
    TreeCollection.prototype._add = function (newItem, index, parent, key) {
        if (index === void 0) { index = -1; }
        if (parent === void 0) { parent = this._root; }
        this._setParent(newItem, parent);
        if (key > 0 && index !== -1) {
            index = index + 1;
        }
        var id = _super.prototype._add.call(this, newItem, index);
        if (Array.isArray(newItem.items)) {
            for (var _i = 0, _a = newItem.items; _i < _a.length; _i++) {
                var item = _a[_i];
                this.add(item, -1, newItem.id);
            }
        }
        return id;
    };
    TreeCollection.prototype._setParent = function (item, parent) {
        item.parent = item.parent ? item.parent.toString() : parent;
        var parentItem = this._pull[item.parent];
        if (parentItem && !parentItem.items) {
            parentItem.items = [];
        }
    };
    TreeCollection.prototype._copy = function (id, index, target, targetId, key) {
        if (target === void 0) { target = this; }
        if (targetId === void 0) { targetId = this._root; }
        if (!this.exists(id)) {
            return null;
        }
        var currentChilds = this._childs[id];
        if (key) {
            index = index === -1 ? -1 : index + key;
        }
        if (target === this && !this.canCopy(id, targetId)) {
            return null;
        }
        var itemCopy = (0, helpers_1.copyWithoutInner)(this.getItem(id), { items: true });
        if (target.exists(id)) {
            itemCopy.id = (0, core_1.uid)();
        }
        if (!(0, helpers_1.isTreeCollection)(target)) {
            target.add(itemCopy, index);
            return;
        }
        if (this.exists(id)) {
            itemCopy.parent = targetId;
            if (target !== this && targetId === this._root) {
                itemCopy.parent = target.getRoot();
            }
            target.add(itemCopy, index);
            id = itemCopy.id;
        }
        if (currentChilds) {
            for (var _i = 0, currentChilds_1 = currentChilds; _i < currentChilds_1.length; _i++) {
                var child = currentChilds_1[_i];
                var childId = child.id;
                var childIndex = this.getIndex(childId);
                this.copy(childId, childIndex, target, id);
            }
        }
        return id;
    };
    TreeCollection.prototype._move = function (id, index, target, targetId, key) {
        if (target === void 0) { target = this; }
        if (targetId === void 0) { targetId = this._root; }
        if (!this.exists(id)) {
            return null;
        }
        if (key && index < this.getIndex(id)) {
            index = index === -1 ? -1 : index + key;
        }
        if (target !== this) {
            if (!(0, helpers_1.isTreeCollection)(target)) {
                // move to datacollection
                target.add((0, helpers_1.copyWithoutInner)(this.getItem(id)), index);
                this.remove(id);
                return;
            }
            var returnId = this.copy(id, index, target, targetId);
            this.remove(id);
            return returnId;
        }
        // move inside
        if (!this.canCopy(id, targetId)) {
            return null;
        }
        this._moveItem(id, targetId, index);
        this.events.fire(types_1.DataEvents.change, [id, "update", this.getItem(id)]);
        return id;
    };
    TreeCollection.prototype._moveItem = function (id, targetId, index) {
        var parentId = this.getParent(id);
        var childs = this._initChilds || this._childs;
        [this._childs, this._initChilds].forEach(function (store) {
            if (!store || !store[parentId])
                return;
            var i = store[parentId].findIndex(function (item) { return item.id === id; });
            if (i === -1)
                return;
            var item = store[parentId].splice(i, 1)[0];
            item.parent = targetId;
            addToOrder(store, item, targetId, index);
            if (!store[parentId].length)
                delete store[parentId];
        });
        if (parentId !== this._root && parentId !== targetId) {
            var parent_1 = this.getItem(parentId);
            if (childs[parentId]) {
                parent_1.items = __spreadArray([], childs[parentId], true);
            }
            else {
                delete parent_1.items;
            }
        }
        if (targetId !== this._root) {
            var target = this.getItem(targetId);
            target.items = __spreadArray([], (childs[targetId] || []), true);
        }
    };
    TreeCollection.prototype._reset = function (config) {
        var _a;
        if ((0, core_1.isId)(config === null || config === void 0 ? void 0 : config.id)) {
            var childs = __spreadArray([], this._childs[config === null || config === void 0 ? void 0 : config.id], true);
            for (var _i = 0, childs_2 = childs; _i < childs_2.length; _i++) {
                var child = childs_2[_i];
                this.remove(child.id);
            }
        }
        else {
            _super.prototype._reset.call(this, config);
            var root = this._root;
            this._initChilds = null;
            this._childs = (_a = {}, _a[root] = [], _a);
        }
    };
    TreeCollection.prototype._removeCore = function (id) {
        if (this._pull[id]) {
            var parent_2 = this.getParent(id);
            this._childs[parent_2] = this._childs[parent_2].filter(function (item) { return item.id !== id; });
            if (parent_2 !== this._root && !this._childs[parent_2].length) {
                delete this._childs[parent_2];
            }
            if (this._initChilds && this._initChilds[parent_2]) {
                this._initChilds[parent_2] = this._initChilds[parent_2].filter(function (item) { return item.id !== id; });
                if (parent_2 !== this._root && !this._initChilds[parent_2].length) {
                    delete this._initChilds[parent_2];
                }
            }
            this._fastDeleteChilds(this._childs, id);
            if (this._initChilds) {
                this._fastDeleteChilds(this._initChilds, id);
            }
        }
    };
    TreeCollection.prototype._addToOrder = function (_order, obj, index) {
        var childs = this._childs;
        var initChilds = this._initChilds;
        var parent = obj.parent;
        this._pull[obj.id] = obj;
        if (obj.parent &&
            this._pull[obj.parent] &&
            this._pull[obj.parent].items &&
            !this._pull[obj.parent].items.find(function (item) { return item.id === obj.id; })) {
            this._pull[obj.parent].items.push(obj);
        }
        _super.prototype._addToOrder.call(this, _order, obj, index);
        addToOrder(childs, obj, parent, index);
        if (initChilds) {
            addToOrder(initChilds, obj, parent, index);
        }
    };
    TreeCollection.prototype._parse_data = function (data, parent) {
        var _a;
        if (parent === void 0) { parent = this._root; }
        for (var _i = 0, data_1 = data; _i < data_1.length; _i++) {
            var obj = data_1[_i];
            if (this.config.init) {
                obj = this.config.init(obj);
            }
            if (obj && typeof obj !== "object") {
                obj = {
                    value: obj,
                };
            }
            obj.id = (_a = obj.id) !== null && _a !== void 0 ? _a : (0, core_1.uid)();
            obj.parent =
                typeof obj.parent === "undefined" || obj.parent === null || (obj.parent && obj.$items)
                    ? parent
                    : obj.parent;
            if (this._pull[obj.id]) {
                (0, helpers_1.dhxError)("Item ".concat(obj.id, " already exist"));
            }
            this._parseItem(obj);
            if (obj.items && obj.items instanceof Object) {
                this._parse_data(obj.items, obj.id);
            }
        }
    };
    TreeCollection.prototype._parseItem = function (item) {
        this._pull[item.id] = item;
        this._order[this._order.length] = item;
        if (!this._childs[item.parent]) {
            this._childs[item.parent] = [];
        }
        this._childs[item.parent].push(item);
    };
    TreeCollection.prototype._fastDeleteChilds = function (target, id) {
        var _a, _b;
        if (this._pull[id]) {
            if ((_a = this._initFilterOrder) === null || _a === void 0 ? void 0 : _a.length) {
                this._initFilterOrder = this._initFilterOrder.filter(function (el) { return el.id !== id; });
            }
            if ((_b = this._initSortOrder) === null || _b === void 0 ? void 0 : _b.length) {
                this._initSortOrder = this._initSortOrder.filter(function (el) { return el.id !== id; });
            }
            this._order = this._order.filter(function (el) { return el.id !== id; });
            delete this._pull[id];
        }
        if (!target[id]) {
            return;
        }
        for (var i = 0; i < target[id].length; i++) {
            this._fastDeleteChilds(target, target[id][i].id);
        }
        delete target[id];
    };
    TreeCollection.prototype._recursiveFilter = function (rule, config, current, level, newChilds) {
        var _this = this;
        var childs = this._childs[current];
        if (!childs) {
            return;
        }
        var condition = function (item) {
            switch (config.type) {
                case types_1.TreeFilterType.all: {
                    return true;
                }
                case types_1.TreeFilterType.level: {
                    return level === config.level;
                }
                case types_1.TreeFilterType.leafs: {
                    return !_this.haveItems(item.id);
                }
            }
        };
        if (typeof rule === "function") {
            var customRule = function (item) { return condition(item) && rule(item); };
            var filtered = childs.filter(customRule);
            if (filtered.length) {
                newChilds[current] = filtered;
            }
            else if (current === this._root) {
                newChilds[current] = [];
            }
        }
        else {
            var customRule = function (item) {
                var _a;
                var responseOfRule = true;
                for (var compare in rule) {
                    if (rule[compare].by && rule[compare].match !== "") {
                        responseOfRule = rule[compare].compare
                            ? rule[compare].compare(item[rule[compare].by], rule[compare].match, item)
                            : ((_a = item[rule[compare].by]) === null || _a === void 0 ? void 0 : _a.toString().toLocaleLowerCase().indexOf(rule[compare].match.toString().toLowerCase())) !== -1;
                    }
                    if (!responseOfRule)
                        break;
                }
                return condition(item) && responseOfRule;
            };
            var filtered = childs.filter(customRule);
            if (filtered.length) {
                newChilds[current] = filtered;
            }
            else if (current === this._root) {
                newChilds[current] = [];
            }
        }
        for (var _i = 0, childs_3 = childs; _i < childs_3.length; _i++) {
            var child = childs_3[_i];
            this._recursiveFilter(rule, config, child.id, level + 1, newChilds);
        }
    };
    TreeCollection.prototype._serialize = function (parent, fn) {
        var _this = this;
        if (parent === void 0) { parent = this._root; }
        return this.map(function (item) {
            var itemCopy = {};
            for (var key in item) {
                if (key === "parent" || key === "items" || key.startsWith("$")) {
                    continue;
                }
                itemCopy[key] = item[key];
            }
            if (fn) {
                itemCopy = fn(itemCopy);
            }
            if (_this.haveItems(item.id)) {
                itemCopy.items = _this._serialize(item.id, fn);
            }
            return itemCopy;
        }, parent, false);
    };
    TreeCollection.prototype._applyFilter = function (rule, config) {
        var _this = this;
        if (!rule || (typeof rule !== "function" && (0, core_1.isEmptyObj)(rule)))
            return;
        if (!this._checkFilterRule(rule)) {
            throw new Error("Invalid filter rule");
        }
        if (!this._initFilterOrder) {
            this._initFilterOrder = this._order;
        }
        if (!this._initChilds) {
            this._initChilds = this._childs;
        }
        var filter;
        var newChilds = {};
        if (typeof rule !== "function") {
            filter = {};
            if ((0, core_1.isDefined)(rule.by)) {
                filter[rule.by] = rule;
            }
            else {
                for (var key in rule) {
                    filter[key] = rule[key];
                }
            }
        }
        else {
            filter = rule;
        }
        this._recursiveFilter(filter, config, this._root, 0, newChilds);
        Object.keys(newChilds).forEach(function (key) {
            var parentId = _this.getParent(key);
            var current = _this.getItem(key);
            while (parentId) {
                if (!newChilds[parentId]) {
                    newChilds[parentId] = [];
                }
                if (current && !newChilds[parentId].find(function (x) { return x.id === current.id; })) {
                    newChilds[parentId].push(current);
                }
                current = _this.getItem(parentId);
                parentId = _this.getParent(parentId);
            }
        });
        this._childs = newChilds;
    };
    TreeCollection.prototype._normalizeFilters = function (filters) {
        var rules = {};
        for (var key in filters) {
            rules[key] = filters[key].rule;
        }
        return rules;
    };
    TreeCollection.prototype._checkFilterRule = function (rule) {
        var _this = this;
        return (_super.prototype._checkFilterRule.call(this, rule) ||
            Object.values(rule).every(function (value) { return typeof value !== "function" && _super.prototype._checkFilterRule.call(_this, value); }));
    };
    TreeCollection.prototype._applySorters = function (by) {
        for (var key in this._childs) {
            this._sort.sort(this._childs[key], by, this._sorter);
        }
        if (this._initChilds && Object.keys(this._initChilds).length) {
            for (var key in this._initChilds) {
                this._sort.sort(this._initChilds[key], by, this._sorter);
            }
        }
    };
    return TreeCollection;
}(datacollection_1.DataCollection));
exports.TreeCollection = TreeCollection;


/***/ }),
/* 52 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.dragManager = void 0;
var html_1 = __webpack_require__(4);
var CollectionStore_1 = __webpack_require__(53);
var types_1 = __webpack_require__(5);
var helpers_1 = __webpack_require__(3);
var core_1 = __webpack_require__(0);
function getVerticalPosition(e) {
    var y = e.targetTouches
        ? e.targetTouches[0].clientY
        : e.clientY;
    var element = (0, html_1.locateNode)((0, html_1.getElementFromPoint)(e));
    if (!element) {
        return null;
    }
    var treeLine = element.childNodes[0];
    if (treeLine) {
        var _a = treeLine.getBoundingClientRect(), top_1 = _a.top, height = _a.height;
        return (y - top_1) / height;
    }
}
function dragEventContent(element, elements, exhaustiveList) {
    if (exhaustiveList === void 0) { exhaustiveList = false; }
    var rect = element.getBoundingClientRect();
    var ghost = document.createElement("div");
    var clone = element.cloneNode(true);
    var fontSize = window.getComputedStyle(element.parentElement).fontSize;
    clone.style.width = rect.width + "px";
    clone.style.height = rect.height + "px";
    clone.style.maxHeight = rect.height + "px";
    clone.style.opacity = "0.8";
    clone.style.fontSize = fontSize;
    if (!exhaustiveList || !elements || !elements.length) {
        ghost.appendChild(clone);
    }
    if (elements && elements.length) {
        elements.forEach(function (node, key) {
            var nodeClone = node.cloneNode(true);
            if (!nodeClone.style.getPropertyPriority("width")) {
                nodeClone.style.width = rect.width + "px";
            }
            nodeClone.style.height = rect.height + "px";
            nodeClone.style.maxHeight = rect.height + "px";
            nodeClone.style.top = (key + 1) * 12 - rect.height - rect.height * key + "px";
            nodeClone.style.left = (key + 1) * 12 + "px";
            nodeClone.style.opacity = "0.6";
            nodeClone.style.zIndex = "".concat(-key - 1);
            if (!exhaustiveList) {
                nodeClone.style.fontSize = fontSize;
                nodeClone.style.overflow = "hidden";
            }
            ghost.appendChild(nodeClone);
        });
    }
    ghost.className = "dhx_drag-ghost";
    return ghost;
}
var isGrid = function (component) { var _a; return (_a = component.name) === null || _a === void 0 ? void 0 : _a.includes("grid"); };
var isProGrid = function (component) {
    return isGrid(component) && component.hasOwnProperty("scrollView");
};
var DragManager = /** @class */ (function () {
    function DragManager() {
        var _this = this;
        this._transferData = {};
        this._canMove = true;
        this._isDrag = false;
        this._lastOverItemId = null;
        this._onMouseMove = function (e) {
            if (!_this._transferData.start) {
                return;
            }
            var element = (0, html_1.getElementFromPoint)(e);
            var overAreaType = (0, html_1.locate)(element, "data-dhx-drop-area");
            var isCommonDropArea = overAreaType === "common";
            var pageX = e.targetTouches ? e.targetTouches[0].pageX : e.pageX;
            var pageY = e.targetTouches ? e.targetTouches[0].pageY : e.pageY;
            if (_this._transferData.type === "column" && overAreaType === "group") {
                _this._transferData.type = "group";
                _this._transferData.isWasColumn = true;
            }
            if (_this._transferData.type === "group" &&
                overAreaType === "column" &&
                _this._transferData.isWasColumn &&
                !_this._transferData.groupOnly) {
                _this._transferData.type = "column";
            }
            var targetId = (0, html_1.locate)(element);
            if (overAreaType === _this._transferData.type || isCommonDropArea) {
                var _a = _this._transferData, x = _a.x, y = _a.y, start = _a.start, componentId = _a.componentId;
                if (!_this._transferData.ghost) {
                    if (Math.abs(x - pageX) < 3 && Math.abs(y - pageY) < 3) {
                        return;
                    }
                    else {
                        var ghost = _this._onDragStart(start, componentId, e);
                        if (!ghost) {
                            _this._endDrop(e);
                            return;
                        }
                        else {
                            _this._transferData.ghost = ghost;
                            document.body.appendChild(_this._transferData.ghost);
                        }
                    }
                }
                var targetComponentId = (0, html_1.locate)(element, "data-dhx-widget-id");
                if ((overAreaType === "column" || overAreaType === "group") &&
                    targetComponentId === componentId) {
                    if (targetId !== _this._lastOverItemId) {
                        _this._lastOverItemId = targetId;
                        _this._onDrag(e);
                    }
                }
                else {
                    _this._onDrag(e);
                }
            }
            else if (_this._canMove) {
                _this.cancelCanDrop(e);
            }
            _this._notAllowedDropArea((overAreaType !== _this._transferData.type && !isCommonDropArea) ||
                (!_this._transferData.groupable && overAreaType === "group") ||
                (_this._transferData.groupOnly &&
                    overAreaType !== "group" &&
                    !isCommonDropArea &&
                    _this._transferData.start != targetId));
            _this._moveGhost(pageX, pageY);
        };
        this._onMouseUp = function (e) {
            if (!_this._transferData.x) {
                return;
            }
            if (_this._transferData.ghost) {
                _this._removeGhost();
                _this._onDrop(e);
            }
            else {
                _this._endDrop(e);
            }
            if (!e.targetTouches) {
                document.removeEventListener("mousemove", _this._onMouseMove);
                document.removeEventListener("mouseup", _this._onMouseUp);
            }
            else {
                document.removeEventListener("touchmove", _this._onMouseMove);
                document.removeEventListener("touchend", _this._onMouseUp);
            }
        };
    }
    DragManager.prototype.setItem = function (id, item) {
        CollectionStore_1.collectionStore.setItem(id, item);
    };
    DragManager.prototype.onMouseDown = function (_a) {
        var event = _a.event, itemsForGhost = _a.itemsForGhost, ghost = _a.ghost, source = _a.source, type = _a.type, groupable = _a.groupable, groupOnly = _a.groupOnly;
        // onmousedown only for target objects
        if (event.which !== 1 && !event.targetTouches) {
            return;
        }
        if (!event.targetTouches) {
            document.addEventListener("mousemove", this._onMouseMove);
            document.addEventListener("mouseup", this._onMouseUp);
        }
        else {
            document.addEventListener("touchmove", this._onMouseMove, false);
            document.addEventListener("touchend", this._onMouseUp, false);
        }
        var element = (0, html_1.getElementFromPoint)(event);
        var item = (0, html_1.locateNode)(element, "data-dhx-id");
        var id = item && item.getAttribute("data-dhx-id");
        var component = (0, html_1.locateNode)(element, "data-dhx-widget-id");
        var componentId = component === null || component === void 0 ? void 0 : component.getAttribute("data-dhx-widget-id");
        if (ghost)
            this._ghost = ghost;
        if (Array.isArray(source) && source.includes(id)) {
            this._transferData.source = __spreadArray([], source, true);
            this._itemsForGhost = itemsForGhost;
        }
        else {
            this._transferData.source = [id];
            this._itemsForGhost = null;
        }
        if (id && componentId) {
            var itemBox = (0, html_1.getBox)(item);
            var left = isGrid(CollectionStore_1.collectionStore.getItem(componentId)) ? (0, html_1.getBox)(component).left : itemBox.left;
            var pageX = event.targetTouches
                ? event.targetTouches[0].pageX
                : event.pageX;
            var pageY = event.targetTouches
                ? event.targetTouches[0].pageY
                : event.pageY;
            this._transferData.initXOffset = type === "row" ? pageX - left : 0;
            this._transferData.initYOffset = type === "row" ? pageY - itemBox.top : 0;
            this._transferData.x = pageX;
            this._transferData.y = pageY;
            this._transferData.componentId = componentId;
            this._transferData.start = id;
            this._transferData.item = item;
            this._transferData.type = type;
            this._transferData.groupable = groupable;
            this._transferData.groupOnly = groupOnly;
        }
    };
    DragManager.prototype.isDrag = function () {
        return this._isDrag;
    };
    DragManager.prototype.cancelCanDrop = function (event) {
        this._canMove = false;
        var _a = this._transferData, start = _a.start, source = _a.source, target = _a.target, dropComponentId = _a.dropComponentId, type = _a.type;
        var data = {
            start: start,
            source: source,
            target: target,
            dragItem: type,
        };
        var collection = CollectionStore_1.collectionStore.getItem(dropComponentId);
        if (collection && target) {
            collection.events.fire(types_1.DragEvents.cancelDrop, [data, event, this._transferData.type]);
        }
        this._transferData.dropComponentId = null;
        this._transferData.target = null;
    };
    DragManager.prototype._moveGhost = function (x, y) {
        if (this._transferData.ghost) {
            this._transferData.ghost.style.left = x - this._transferData.initXOffset + "px";
            this._transferData.ghost.style.top = y - this._transferData.initYOffset + "px";
        }
    };
    DragManager.prototype._removeGhost = function () {
        this._ghost = null;
        document.body.removeChild(this._transferData.ghost);
    };
    DragManager.prototype._onDrop = function (e) {
        if (!this._canMove) {
            this._endDrop(e);
            return;
        }
        var _a = this._transferData, start = _a.start, source = _a.source, target = _a.target, dropComponentId = _a.dropComponentId, dropPosition = _a.dropPosition;
        var data = { start: start, source: source, target: target, dropPosition: dropPosition };
        var component = CollectionStore_1.collectionStore.getItem(dropComponentId);
        var config = component && component.config;
        if (!component || config.dragMode === "source") {
            this._endDrop(e);
            return;
        }
        if (component.events.fire(types_1.DragEvents.beforeDrop, [data, e, this._transferData.type])) {
            var to = {
                id: target,
                component: component,
            };
            var from = {
                id: start,
                component: this._transferData.component,
                newId: null,
            };
            this._move(from, to);
            if (from.newId && from.component !== to.component)
                data.start = from.newId;
            to.component.events.fire(types_1.DragEvents.afterDrop, [data, e, this._transferData.type]);
        }
        this._endDrop(e);
    };
    DragManager.prototype._onDragStart = function (id, componentId, e) {
        var component = CollectionStore_1.collectionStore.getItem(componentId);
        var config = component.config;
        var _a = this._transferData, start = _a.start, source = _a.source, target = _a.target;
        var data = {
            start: start,
            source: source,
            target: target,
        };
        if (config.dragMode === "target" || component._pregroupData) {
            return null;
        }
        var ghost = this._transferData.type === "row"
            ? dragEventContent(this._transferData.item, this._itemsForGhost, isGrid(component))
            : this._ghost;
        var ans = component.events.fire(types_1.DragEvents.beforeDrag, [data, e, ghost, this._transferData.type]);
        if (!ans || !(0, core_1.isId)(id)) {
            return null;
        }
        component.events.fire(types_1.DragEvents.dragStart, [data, e, this._transferData.type]);
        this._isDrag = true;
        this._toggleTextSelection(true);
        this._transferData.component = component;
        this._transferData.dragConfig = config;
        return ghost;
    };
    DragManager.prototype._onDrag = function (e) {
        var element = (0, html_1.getElementFromPoint)(e);
        var collectionId = (0, html_1.locate)(element, "data-dhx-widget-id");
        var component = CollectionStore_1.collectionStore.getItem(collectionId);
        if (!component) {
            if (this._canMove) {
                this.cancelCanDrop(e);
            }
            return;
        }
        var isTreeHeaderOrFooter = !!(0, html_1.locateNodeByClassName)(element, "dhx_grid-header") ||
            !!(0, html_1.locateNodeByClassName)(element, "dhx_grid-footer");
        var gridConfig = component.config.columns
            ? component.config
            : undefined;
        var isColumnDrag = gridConfig && (gridConfig.dragItem === "both" || gridConfig.dragItem === "column");
        if (isTreeHeaderOrFooter && !isColumnDrag) {
            if (this._canMove) {
                this.cancelCanDrop(e);
            }
            return;
        }
        var id = (0, html_1.locate)(element, "data-dhx-id");
        var rootId = (0, html_1.locate)(element, "data-dhx-root-id");
        if (!id && !rootId) {
            this.cancelCanDrop(e);
            this._transferData.dropComponentId = collectionId;
            this._transferData.target = null;
            this._canDrop(e);
            return;
        }
        var _a = this._transferData, dropComponentId = _a.dropComponentId, start = _a.start, source = _a.source, target = _a.target, componentId = _a.componentId, dropPosition = _a.dropPosition;
        if (component.config.dropBehaviour === "complex") {
            var pos = getVerticalPosition(e);
            if (component.config.group) {
                if (pos <= 0.5) {
                    this._transferData.dropPosition = "top";
                }
                else {
                    this._transferData.dropPosition = "bottom";
                }
            }
            else {
                if (pos <= 0.25) {
                    this._transferData.dropPosition = "top";
                }
                else if (pos >= 0.75) {
                    this._transferData.dropPosition = "bottom";
                }
                else {
                    this._transferData.dropPosition = "in";
                }
            }
        }
        else if ((target === id || (!id && target === rootId)) && dropComponentId === collectionId) {
            return;
        }
        var from = {
            id: start,
            component: this._transferData.component,
        };
        if (component.config.dragMode === "source") {
            return;
        }
        from.component.events.fire(types_1.DragEvents.dragOut, [
            {
                start: start,
                source: source,
                target: target,
            },
            e,
            this._transferData.type,
        ]);
        if (collectionId !== componentId ||
            !(0, helpers_1.isTreeCollection)(from.component.data) ||
            ((0, helpers_1.isTreeCollection)(from.component.data) && from.component.data.canCopy(from.id, id))) {
            this.cancelCanDrop(e); // clear last
            var target_1 = (this._transferData.target = id || rootId);
            this._transferData.dropComponentId = collectionId;
            var canMove = from.component.events.fire(types_1.DragEvents.dragIn, [
                {
                    start: start,
                    source: source,
                    target: target_1,
                    dropPosition: dropPosition,
                },
                e,
                this._transferData.type,
            ]);
            if (canMove) {
                this._canDrop(e);
            }
        }
        else {
            this.cancelCanDrop(e);
        }
    };
    DragManager.prototype._move = function (from, to) {
        var grid = from.component;
        var nextGrid = to.component;
        var fromData = from.component.data;
        var toData = to.component.data;
        var index = 0;
        var componentId = to.id;
        var behaviour = (0, helpers_1.isTreeCollection)(toData) ? to.component.config.dropBehaviour : undefined;
        var gridConfig = from.component.config.columns
            ? from.component.config
            : undefined;
        var isColumnDrag = gridConfig &&
            (gridConfig.dragItem === "both" || gridConfig.dragItem === "column") &&
            gridConfig.columns.map(function (c) { return c.id; }).filter(function (id) { return id === from.id || id === to.id; }).length;
        if (isColumnDrag && from.component === to.component) {
            if (from.id === to.id)
                return;
            var currentCols = grid.config.columns.map(function (c) { return (__assign({}, c)); });
            var sourceIndex = currentCols.findIndex(function (c) { return c.id === from.id; });
            var componentIndex = currentCols.findIndex(function (c) { return c.id === to.id; });
            if (componentIndex === -1)
                return;
            currentCols.splice(componentIndex, 0, currentCols.splice(sourceIndex, 1)[0]);
            grid.setColumns(currentCols);
            grid.paint();
            return;
        }
        else if (isColumnDrag &&
            from.component &&
            isProGrid(from.component) &&
            to.component &&
            isProGrid(to.component)) {
            var currentCols = grid.config.columns.map(function (c) { return (__assign({}, c)); });
            var sourceIndex = currentCols.findIndex(function (c) { return c.id === from.id; });
            var nextGridCols = nextGrid.config.columns.map(function (c) { return (__assign({}, c)); });
            var nextGridLength = nextGrid.data.getLength();
            var componentIndex = nextGridCols.findIndex(function (c) { return c.id === to.id; });
            var currentColumnData_1 = [];
            var currentColumnId_1 = from.id;
            grid.data.forEach(function (item) {
                var _a;
                currentColumnData_1.push((_a = { id: item.id }, _a[currentColumnId_1] = item[from.id], _a));
            });
            if (nextGridLength) {
                grid.data.forEach(function (item, index) {
                    var nextGridItem = nextGrid.data.getItem(item.id);
                    if (nextGridItem) {
                        nextGrid.data.update(nextGridItem.id, __assign(__assign({}, nextGridItem), currentColumnData_1[index]));
                    }
                    else {
                        nextGrid.data.add(currentColumnData_1[index]);
                    }
                });
            }
            else {
                nextGrid.data.parse(currentColumnData_1);
            }
            var col = currentCols.splice(sourceIndex, 1)[0];
            nextGridCols.find(function (c) { return c.id === currentColumnId_1; }) || nextGridCols.splice(componentIndex, 0, col);
            nextGrid.setColumns(nextGridCols);
            nextGrid.paint();
            grid.setColumns(currentCols);
            grid.paint();
            return;
        }
        var isRootParent = to.id === nextGrid.config.rootParent;
        switch (behaviour) {
            case "child":
                break;
            case "sibling":
                componentId = toData.getParent(componentId);
                index = toData.getIndex(to.id) + 1;
                break;
            case "complex": {
                var dropPosition = this._transferData.dropPosition;
                if (isRootParent) {
                    componentId = to.id;
                    index = toData.getLength();
                }
                else {
                    var fromIndex = toData.getIndex(from.id);
                    if (dropPosition === "top") {
                        componentId = toData.getParent(componentId);
                        index =
                            toData.getIndex(to.id) -
                                (fromIndex === -1 || fromIndex > toData.getIndex(to.id) ? 0 : 1);
                    }
                    else if (dropPosition === "bottom") {
                        componentId = toData.getParent(componentId);
                        index =
                            toData.getIndex(to.id) +
                                (fromIndex === -1 || fromIndex > toData.getIndex(to.id) ? 1 : 0);
                    }
                }
                break;
            }
            default:
                // list move
                if (!(0, core_1.isId)(to.id)) {
                    index = -1;
                }
                else {
                    if (toData.getIndex(from.id) > -1)
                        from.newId = (0, core_1.uid)();
                    index = toData.getIndex(to.id);
                }
        }
        if (this._transferData.dragConfig.dragCopy) {
            if (this._transferData.source instanceof Array && this._transferData.source.length > 1) {
                this._transferData.source.map(function (selctedId) {
                    fromData.copy(selctedId, index, toData, componentId);
                    if (index > -1) {
                        index++;
                    }
                });
            }
            else {
                fromData.copy(from.id, index, toData, componentId);
            }
        }
        else {
            if (this._transferData.source instanceof Array && this._transferData.source.length > 1) {
                fromData.move(this._transferData.source, index, toData, componentId);
            }
            else {
                if (isGrid(nextGrid) && !nextGrid.config.columns.length) {
                    var gridItem = grid.data.getItem(from.id);
                    nextGrid.data.parse([__assign({}, gridItem)]);
                    nextGrid.setColumns(__spreadArray([], grid.config.columns, true));
                    nextGrid.paint();
                    grid.data.remove(from.id);
                    grid.paint();
                }
                else {
                    fromData.move(from.id, index, toData, componentId, from.newId);
                }
            }
        }
    };
    DragManager.prototype._endDrop = function (e) {
        this._toggleTextSelection(false);
        this._notAllowedDropArea(false);
        if (this._transferData.component) {
            var _a = this._transferData, start = _a.start, source = _a.source, target = _a.target;
            var data = { start: start, source: source, target: target };
            this._transferData.component.events.fire(types_1.DragEvents.afterDrag, [
                data,
                e,
                this._transferData.type,
            ]);
        }
        this.cancelCanDrop(e);
        this._canMove = true;
        this._isDrag = false;
        this._lastOverItemId = null;
        this._transferData = {};
        this._transferData.target = null;
        this._transferData.dropComponentId = null;
    };
    DragManager.prototype._canDrop = function (e) {
        this._canMove = true;
        var _a = this._transferData, start = _a.start, source = _a.source, target = _a.target, dropPosition = _a.dropPosition;
        var data = {
            start: start,
            source: source,
            target: target,
            dropPosition: dropPosition,
        };
        var component = CollectionStore_1.collectionStore.getItem(this._transferData.dropComponentId);
        if (component && this._transferData.target) {
            component.events.fire(types_1.DragEvents.canDrop, [data, e, this._transferData.type]);
        }
    };
    DragManager.prototype._toggleTextSelection = function (add) {
        if (add) {
            document.body.classList.add("dhx_no-select");
        }
        else {
            document.body.classList.remove("dhx_no-select");
        }
    };
    DragManager.prototype._notAllowedDropArea = function (notAllowed) {
        if (notAllowed) {
            if (!document.body.classList.contains("dhx_drop-area--not-allowed")) {
                document.body.classList.add("dhx_drop-area--not-allowed");
            }
        }
        else {
            document.body.classList.remove("dhx_drop-area--not-allowed");
        }
    };
    return DragManager;
}());
var dhx = (window.dhxHelpers = window.dhxHelpers || {});
dhx.dragManager = dhx.dragManager || new DragManager();
exports.dragManager = dhx.dragManager;


/***/ }),
/* 53 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.collectionStore = exports.CollectionStore = void 0;
var core_1 = __webpack_require__(0);
var CollectionStore = /** @class */ (function () {
    function CollectionStore() {
        this._store = {};
    }
    CollectionStore.prototype.setItem = function (id, target) {
        this._store[id] = target;
    };
    CollectionStore.prototype.getItem = function (id) {
        if (!(0, core_1.isId)(id) || !this._store[id]) {
            return null;
        }
        return this._store[id];
    };
    return CollectionStore;
}());
exports.CollectionStore = CollectionStore;
var dhx = (window.dhxHelpers = window.dhxHelpers || {});
dhx.collectionStore = dhx.collectionStore || new CollectionStore();
exports.collectionStore = dhx.collectionStore;


/***/ }),
/* 54 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(Promise) {
var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
Object.defineProperty(exports, "__esModule", { value: true });
exports.LazyDataProxy = void 0;
var dataproxy_1 = __webpack_require__(8);
var core_1 = __webpack_require__(0);
var ajax_1 = __webpack_require__(14);
var LazyDataProxy = /** @class */ (function (_super) {
    __extends(LazyDataProxy, _super);
    function LazyDataProxy(url, config) {
        var _this = _super.call(this, url) || this;
        _this.config = (0, core_1.extend)({
            from: 0,
            limit: 50,
            delay: 50,
            prepare: 0,
        }, config);
        _this.updateUrl(url, { from: _this.config.from, limit: _this.config.limit });
        return _this;
    }
    LazyDataProxy.prototype.load = function () {
        var _this = this;
        return new Promise(function (resolve, reject) {
            if (!_this._timeout) {
                ajax_1.ajax.get(_this.url, { responseType: "text" })
                    .then(resolve)
                    .catch(reject);
                _this._cooling = true;
                _this._timeout = setTimeout(function () {
                    return;
                });
            }
            else {
                clearTimeout(_this._timeout);
                _this._timeout = setTimeout(function () {
                    ajax_1.ajax.get(_this.url, { responseType: "text" })
                        .then(resolve)
                        .catch(reject);
                    _this._cooling = true;
                }, _this.config.delay);
                if (_this._cooling) {
                    resolve(null);
                    _this._cooling = false;
                }
            }
        });
    };
    return LazyDataProxy;
}(dataproxy_1.DataProxy));
exports.LazyDataProxy = LazyDataProxy;

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(6)))

/***/ }),
/* 55 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.Selection = void 0;
var events_1 = __webpack_require__(13);
var types_1 = __webpack_require__(56);
var types_2 = __webpack_require__(5);
var core_1 = __webpack_require__(0);
var Selection = /** @class */ (function () {
    function Selection(config, data, events) {
        var _this = this;
        this.events = events || new events_1.EventSystem(this);
        this.config = config;
        this._data = data;
        this._selected = null;
        this._data.events.on(types_2.DataEvents.removeAll, function () {
            _this._selected = null;
        });
        this._data.events.on(types_2.DataEvents.change, function () {
            if ((0, core_1.isId)(_this._selected)) {
                var near = _this._data.getNearId(_this._selected);
                if (near !== _this._selected) {
                    _this._selected = null;
                    if (near) {
                        _this.add(near);
                    }
                }
            }
        });
    }
    Selection.prototype.getId = function () {
        return this._selected;
    };
    Selection.prototype.getItem = function () {
        if ((0, core_1.isId)(this._selected)) {
            return this._data.getItem(this._selected);
        }
        return null;
    };
    Selection.prototype.remove = function (id) {
        id = id !== null && id !== void 0 ? id : this._selected;
        if (!(0, core_1.isDefined)(id)) {
            return true;
        }
        if (this.events.fire(types_1.SelectionEvents.beforeUnSelect, [id])) {
            this._data.update(id, { $selected: false }, true);
            this._selected = null;
            this.events.fire(types_1.SelectionEvents.afterUnSelect, [id]);
            return true;
        }
        return false;
    };
    Selection.prototype.add = function (id) {
        if (this._selected === id || !!this.config.disabled || !this._data.exists(id)) {
            return;
        }
        this.remove();
        this._addSingle(id);
    };
    Selection.prototype.enable = function () {
        this.config.disabled = false;
    };
    Selection.prototype.disable = function () {
        this.remove();
        this.config.disabled = true;
    };
    Selection.prototype._addSingle = function (id) {
        if (this.events.fire(types_1.SelectionEvents.beforeSelect, [id])) {
            this._selected = id;
            this._data.update(id, { $selected: true }, true);
            this.events.fire(types_1.SelectionEvents.afterSelect, [id]);
        }
    };
    return Selection;
}());
exports.Selection = Selection;


/***/ }),
/* 56 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.SelectionEvents = void 0;
var SelectionEvents;
(function (SelectionEvents) {
    SelectionEvents["beforeUnSelect"] = "beforeunselect";
    SelectionEvents["afterUnSelect"] = "afterunselect";
    SelectionEvents["beforeSelect"] = "beforeselect";
    SelectionEvents["afterSelect"] = "afterselect";
})(SelectionEvents || (exports.SelectionEvents = SelectionEvents = {}));


/***/ }),
/* 57 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Chart = void 0;
var dom_1 = __webpack_require__(1);
var events_1 = __webpack_require__(13);
var view_1 = __webpack_require__(58);
var ts_data_1 = __webpack_require__(7);
var ComposeLayer_1 = __webpack_require__(59);
var Legend_1 = __webpack_require__(61);
var types_1 = __webpack_require__(9);
var index_1 = __webpack_require__(63);
var index_2 = __webpack_require__(67);
var Stacker_1 = __webpack_require__(78);
var common_1 = __webpack_require__(2);
var Tooltip_1 = __webpack_require__(79);
var core_1 = __webpack_require__(0);
var Export_1 = __webpack_require__(85);
var Chart = /** @class */ (function (_super) {
    __extends(Chart, _super);
    function Chart(node, config) {
        if (config === void 0) { config = {}; }
        var _this = this;
        var _a;
        _this = _super.call(this, null, config) || this;
        // using zero values ensure that widget will not attempt to render self in the hidden state
        _this._width = 0;
        _this._height = 0;
        _this._left = 0;
        _this._top = 0;
        var dataConfig = {};
        if (config.maxPoints) {
            dataConfig.approximate = {
                value: (_a = config.series) === null || _a === void 0 ? void 0 : _a.map(function (a) { return a.value; }),
                maxNum: config.maxPoints,
            };
        }
        if (Array.isArray(config.data)) {
            _this.events = new events_1.EventSystem(_this);
            _this.data =
                config.type === "treeMap"
                    ? new ts_data_1.TreeCollection(dataConfig, _this.events)
                    : new ts_data_1.DataCollection(dataConfig, _this.events);
            _this.data.parse(config.data);
        }
        else if (config.data && config.data.events) {
            _this.data = config.data;
            _this.events = _this.data.events;
            _this.events.context = _this;
        }
        else {
            _this.events = new events_1.EventSystem(_this);
            _this.data =
                config.type === "treeMap"
                    ? new ts_data_1.TreeCollection(dataConfig, _this.events)
                    : new ts_data_1.DataCollection(dataConfig, _this.events);
        }
        _this._globalHTMLHandlers = {
            onmousemove: function (e) {
                var _a = _this._layers.getSizes(), left = _a.left, top = _a.top, bottom = _a.bottom, right = _a.right;
                var pageX = e.pageX, pageY = e.pageY;
                var rects = _this.getRootView().node.el.getBoundingClientRect();
                _this._left = rects.left + window.pageXOffset;
                _this._top = rects.top + window.pageYOffset;
                var x = pageX - left - _this._left;
                var y = pageY - top - _this._top;
                if (x >= 0 && x <= _this._width - right - left && y >= 0 && y <= _this._height - bottom - top) {
                    _this.events.fire(types_1.ChartEvents.chartMouseMove, [x, y, _this._left + left, _this._top + top]);
                }
                else {
                    _this.events.fire(types_1.ChartEvents.chartMouseLeave);
                }
            },
            onmouseleave: function () { return _this.events.fire(types_1.ChartEvents.chartMouseLeave); },
        };
        _this._layers = new ComposeLayer_1.ComposeLayer();
        _this.setConfig(config);
        _this._initEvents();
        _this._tooltip = new Tooltip_1.Tooltip(_this);
        var render = function () {
            if (!_this.data.getLength()) {
                return (0, dom_1.el)("div");
            }
            var getChartAriaLabel = function (config) {
                /*  Full example (without symbols of new line):
                    Type chart "Title". X/Y Scale Name: bottom axis from Min to Max. Scale Name: left axis Name from Min to Max.
                    Series: seria A, seria B, seria C, ...
                 */
                var scales = _this._scales;
                var series = config.series;
                var type = config.type;
                if (!type) {
                    // scatter chart
                    type = series && series.length && series[0] ? series[0].type || "" : "";
                }
                var label = "".concat(type || "", " chart.");
                var getAxisName = function (cfg) { return (cfg._isXDirection ? "X" : "Y"); };
                var getAxisTitle = function (cfg) { return cfg.title || cfg.text || cfg.value || ""; };
                var getAxisMin = function (cfg) { return (cfg.steps && cfg.steps.length ? cfg.steps[0] : cfg.min || 0); };
                var getAxisMax = function (cfg) {
                    return cfg.steps && cfg.steps.length
                        ? cfg.steps[cfg.steps.length - 1]
                        : cfg.max || cfg.maxPoints || "max";
                };
                var getRadialAxisValue = function (attr, point) { return point[attr]; };
                if (scales) {
                    Object.keys(scales).forEach(function (direction) {
                        var scale = scales[direction];
                        var cfg = scale._axis || scale.config;
                        if (direction === "radial") {
                            var xData = scale._data._order;
                            label += " X scale ".concat(getAxisTitle(scale.config), ": ").concat(direction, " axis from ").concat(getRadialAxisValue(scale.config.value, xData[0]), " to ").concat(getRadialAxisValue(scale.config.value, xData[xData.length - 1]), ".");
                            label += " ".concat(getAxisName(scale), " scale: axis from ").concat(getAxisMin(cfg), " to ").concat(getAxisMax(cfg), ".");
                        }
                        else {
                            label += " ".concat(getAxisName(scale), " scale ").concat(getAxisTitle(scale.config), ": ").concat(direction, " axis from ").concat(getAxisMin(cfg), " to ").concat(getAxisMax(cfg), ".");
                        }
                    });
                }
                if (series && series.length) {
                    label += " Series:";
                    series.forEach(function (seria, index) {
                        label += " ".concat(type === "pie" || type === "pie3D" || type === "donut" ? seria.text : seria.value);
                        label += index === series.length - 1 ? "." : ",";
                    });
                }
                return label;
            };
            var getChartAriaAttrs = function (config) { return ({
                "aria-label": getChartAriaLabel(config),
            }); };
            var content = [
                (0, dom_1.resizer)(function (x, y) {
                    _this._width = x;
                    _this._height = y || 400; // if height is not provided, use default value
                    var view = _this.getRootView();
                    if (view && view.node && view.node.el) {
                        var rects = view.node.el.getBoundingClientRect();
                        _this._left = rects.left + window.pageXOffset;
                        _this._top = rects.top + window.pageYOffset;
                    }
                    _this.events.fire(types_1.ChartEvents.resize, [_this._width, _this._height]);
                    _this.paint();
                    if (!document.querySelector(".dhx_widget.dhx_chart") && _this._tooltip) {
                        _this._tooltip.destructor();
                    }
                }),
            ];
            if (_this._width && _this._height) {
                content.push(_this._layers.toVDOM(_this._width, _this._height));
            }
            return (0, dom_1.el)(".dhx_widget.dhx_chart", __assign(__assign({ class: config.css ? config.css : "", onmousemove: _this._globalHTMLHandlers.onmousemove, onmouseleave: _this._globalHTMLHandlers.onmouseleave }, getChartAriaAttrs(config)), { tabIndex: 0 }), content);
        };
        var view = (0, dom_1.create)({
            render: render,
            hooks: {
                didMount: function (vm) {
                    if (vm && vm.node && vm.node.parent && vm.node.parent.el) {
                        _this._width = vm.node.parent.el.offsetWidth;
                        _this._height = vm.node.parent.el.offsetHeight || 400;
                        _this.paint();
                    }
                },
            },
        });
        _this.mount(node, view);
        _this.export = new Export_1.Exporter("chart", _this);
        return _this;
    }
    Chart.prototype.getSeries = function (key) {
        return this._series[key];
    };
    Chart.prototype.eachSeries = function (handler) {
        var result = [];
        for (var key in this._series) {
            result.push(handler.call(this, this._series[key]));
        }
        return result;
    };
    Chart.prototype.destructor = function () {
        this._tooltip.destructor();
        this.events.clear();
        this.unmount();
    };
    Chart.prototype.setConfig = function (config) {
        var _this = this;
        var _a;
        if (config.type === "calendarHeatMap") {
            throw new TypeError("The calendarHeatMap chart type is a pro functionality");
        }
        this.config = config;
        this._layers.clear();
        this._series = {};
        this._scales = {};
        var min;
        // let baseLine;
        if (config.scales) {
            for (var key in config.scales) {
                var scale = __assign({}, config.scales[key]);
                if (config.scales[key].min !== undefined) {
                    min = config.scales[key].min;
                }
                scale.type = scale.type || this._detectScaleType(scale, key);
                if (config.scales.radial && key !== "radial") {
                    scale.hidden = true;
                }
                this._setScale(scale, key);
            }
        }
        var stack = new Stacker_1.default();
        this._layers.add(stack);
        (_a = config.series) === null || _a === void 0 ? void 0 : _a.forEach(function (cfg, ind) {
            var _a, _b, _c, _d;
            if (cfg.baseLine !== undefined && cfg.baseLine < min) {
                cfg.baseLine = undefined;
            }
            var serieConfig = __assign({}, cfg);
            serieConfig.type = serieConfig.type || config.type;
            switch (serieConfig.type) {
                case "bar":
                case "xbar":
                case "area":
                case "splineArea":
                    if (!serieConfig.color)
                        serieConfig.color = serieConfig.fill || (0, common_1.getDefaultColor)(ind);
                    if (!serieConfig.fill)
                        serieConfig.fill = serieConfig.color || (0, common_1.getDefaultColor)(ind);
                    break;
                case "treeMap":
                    serieConfig.legendType = ((_a = config.legend) === null || _a === void 0 ? void 0 : _a.type) || "groupName";
                    (_c = (_b = config.legend) === null || _b === void 0 ? void 0 : _b.treeSeries) === null || _c === void 0 ? void 0 : _c.map(function (serie, index) {
                        var _a, _b;
                        serie.active = (_a = serie.active) !== null && _a !== void 0 ? _a : true;
                        serie.id = (_b = serie.id) !== null && _b !== void 0 ? _b : (0, core_1.uid)();
                        if (!serie.color)
                            serie.color = (0, common_1.getDefaultColor)(index, serieConfig.legendType === "range");
                    });
                    serieConfig.treeSeries = (_d = config.legend) === null || _d === void 0 ? void 0 : _d.treeSeries;
                    break;
                case "scatter":
                    if (!serieConfig.pointColor)
                        serieConfig.pointColor = serieConfig.color || (0, common_1.getDefaultColor)(ind);
                    break;
            }
            var chartFactory = index_2.default[serieConfig.type];
            if (serieConfig.barWidth || _this.config.barWidth) {
                serieConfig.barWidth = serieConfig.barWidth || _this.config.barWidth;
            }
            var chart = new chartFactory(_this.data, serieConfig, _this.events);
            var scales = (0, common_1.getScales)(config.scales);
            var chartScales = scales.length > 1 && scales[0] !== "radial"
                ? scales
                : scales[0] === "radial"
                    ? scales
                    : ["bottom", "left"];
            chartScales.forEach(function (type) {
                var scale = _this._scales[type];
                if (!scale) {
                    return;
                }
                chart.addScale(type, scale);
                if (!serieConfig.stacked) {
                    scale.add(chart);
                }
                else {
                    scale.add(stack);
                }
            });
            _this._series[chart.id] = chart;
            if (serieConfig.stacked) {
                stack.add(chart);
            }
            else {
                _this._layers.add(chart);
            }
        });
        if (config.legend) {
            var legendConfig = __assign({}, config.legend);
            if (legendConfig.series) {
                legendConfig.$seriesInfo = legendConfig.series.map(function (id) { return _this._series[id]; });
            }
            var legend = new Legend_1.Legend(this.data, legendConfig, this.events);
            this._layers.add(legend);
        }
        this.paint();
    };
    Chart.prototype._setScale = function (config, position) {
        if (!config.type)
            return;
        var scale = new index_1.default[config.type](this.data, config, position);
        if (scale.config.type !== "radial") {
            this._layers.add(scale.scaleGrid());
        }
        this._layers.add(scale);
        this._scales[position] = scale;
    };
    Chart.prototype._detectScaleType = function (config, key) {
        if (key === "radial") {
            return key;
        }
        if (config.text) {
            return "text";
        }
        return "numeric";
    };
    Chart.prototype._initEvents = function () {
        var _this = this;
        // hide/show series on legend click
        this.events.on(types_1.ChartEvents.toggleSeries, function (id, pieLike) {
            if (_this.config.type === "treeMap") {
                Object.values(_this._series)[0].toggle(id);
                _this.paint();
            }
            else if (pieLike) {
                var serie = _this._series[Object.keys(_this._series)[0]];
                if (serie) {
                    serie.toggle(id);
                    _this.paint();
                }
            }
            else if (_this._series[id]) {
                _this._series[id].toggle();
                _this.paint();
            }
        }, this);
        // repaint on data change
        this.events.on(ts_data_1.DataEvents.change, function () { return _this.paint(); });
        this.events.on(ts_data_1.DataEvents.filter, function () { return _this.paint(); });
    };
    return Chart;
}(view_1.View));
exports.Chart = Chart;


/***/ }),
/* 58 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.toViewLike = exports.View = void 0;
var core_1 = __webpack_require__(0);
var html_1 = __webpack_require__(4);
var View = /** @class */ (function () {
    function View(_container, config) {
        var _a;
        this.config = config || {};
        this._uid = (_a = this.config.rootId) !== null && _a !== void 0 ? _a : (0, core_1.uid)();
    }
    View.prototype.mount = function (container, vnode) {
        if (vnode) {
            this._view = vnode;
        }
        if (container && this._view && this._view.mount) {
            // init view inside of HTML container
            this._container = (0, html_1.toNode)(container);
            if (this._container.tagName) {
                this._view.mount(this._container);
            }
            else if (this._container.attach) {
                this._container.attach(this);
            }
        }
    };
    View.prototype.unmount = function () {
        var rootView = this.getRootView();
        if (rootView && rootView.node) {
            if (this.getRootNode())
                rootView.unmount();
            this._view = null;
        }
    };
    View.prototype.getRootView = function () {
        return this._view;
    };
    View.prototype.getRootNode = function () {
        return this._view && this._view.node && this._view.node.el;
    };
    View.prototype.paint = function () {
        if (this._view && // was mounted
            (this._view.node || // already rendered node
                this._container)) {
            // not rendered, but has container
            this._doNotRepaint = false;
            this._view.redraw();
        }
    };
    return View;
}());
exports.View = View;
function toViewLike(view) {
    return {
        getRootView: function () { return view; },
        paint: function () { return view.node && view.redraw(); },
        mount: function (container) { return view.mount(container); },
    };
}
exports.toViewLike = toViewLike;


/***/ }),
/* 59 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.ComposeLayer = void 0;
var dom_1 = __webpack_require__(1);
var Filters_1 = __webpack_require__(60);
var ComposeLayer = /** @class */ (function () {
    function ComposeLayer() {
        this._data = [];
        this._sizes = { left: 20, right: 20, top: 10, bottom: 10 };
    }
    ComposeLayer.prototype.add = function (obj) {
        this._data.push(obj);
    };
    ComposeLayer.prototype.clear = function () {
        this._data.forEach(function (item) { return item.destructor && item.destructor(); });
        this._data = [];
    };
    ComposeLayer.prototype.getSizes = function () {
        return this._sizes;
    };
    ComposeLayer.prototype.toVDOM = function (width, height) {
        var sizes = { left: 20, right: 20, top: 10, bottom: 10 };
        // eslint-disable-next-line @typescript-eslint/unbound-method
        var toPaint = this._data.filter(function (l) { return !l.dataReady || l.dataReady().length; });
        // eslint-disable-next-line @typescript-eslint/unbound-method
        this._data.forEach(function (l) { return !l.scaleReady || l.scaleReady(sizes); });
        var shift = 0;
        var shiftCount = 0;
        toPaint.forEach(function (item) {
            if (item.seriesShift) {
                shift += item.seriesShift();
                shiftCount++;
            }
        });
        var step = shift / shiftCount;
        shift = shiftCount ? (step - shift) / 2 : 0;
        toPaint.forEach(function (item) {
            if (item.seriesShift) {
                item.seriesShift(shift);
                shift += step;
            }
        });
        this._sizes = sizes;
        var contentWidth = width - sizes.left - sizes.right;
        var contentHeight = height - sizes.top - sizes.bottom;
        var chartsContent = (0, dom_1.sv)("g", {
            transform: "translate(".concat(sizes.left, ", ").concat(sizes.top, ")"),
        }, __spreadArray([
            (0, dom_1.sv)("rect.dhx_chart-graph_area", {
                width: contentWidth > 0 ? contentWidth : 0,
                height: contentHeight > 0 ? contentHeight : 0,
                fill: "transparent",
            })
        ], toPaint.map(function (item) {
            return item.paint(width - (sizes.left + sizes.right), height - (sizes.top + sizes.bottom));
        }), true));
        var defs = (0, dom_1.sv)("defs", [(0, Filters_1.dropShadow)(), (0, Filters_1.shadow)()]);
        return (0, dom_1.sv)("svg", {
            width: width,
            height: height,
            role: "graphics-document",
        }, [defs, chartsContent]);
    };
    return ComposeLayer;
}());
exports.ComposeLayer = ComposeLayer;


/***/ }),
/* 60 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.dropShadow = exports.shadow = void 0;
var dom_1 = __webpack_require__(1);
var shadow = function () {
    return (0, dom_1.sv)("filter", { id: "shadow" }, [
        (0, dom_1.sv)("feDiffuseLighting", {
            in: "SourceGraphic",
            result: "light",
            "lighting-color": "white",
        }, [(0, dom_1.sv)("feDistantLight", { azimuth: 90, elevation: 25 })]),
        (0, dom_1.sv)("feComposite", {
            in: "SourceGraphic",
            in2: "light",
            operator: "arithmetic",
            k1: "1",
            k2: "0",
            k3: "0",
            k4: "0",
        }),
    ]);
};
exports.shadow = shadow;
var dropShadow = function () {
    return (0, dom_1.sv)("filter", {
        id: "dropshadow",
        x: "-100%",
        y: "-100%",
        width: "300%",
        height: "300%",
    }, [
        (0, dom_1.sv)("feGaussianBlur", { in: "SourceAlpha", stdDeviation: 2 }),
        (0, dom_1.sv)("feOffset", { dx: 0, dy: 0, result: "offsetblur" }),
        (0, dom_1.sv)("feOffset", { dx: 0, dy: 0, result: "offsetblur" }),
        (0, dom_1.sv)("feFlood", { "flood-color": "rgba(85,85,85,0.5)" }),
        (0, dom_1.sv)("feComposite", { in2: "offsetblur", operator: "in" }),
        (0, dom_1.sv)("feMerge", [(0, dom_1.sv)("feMergeNode"), (0, dom_1.sv)("feMergeNode", { in: "SourceGraphic" })]),
    ]);
};
exports.dropShadow = dropShadow;


/***/ }),
/* 61 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Legend = void 0;
var ts_data_1 = __webpack_require__(7);
var types_1 = __webpack_require__(9);
var dom_1 = __webpack_require__(1);
var common_1 = __webpack_require__(2);
var legend_1 = __webpack_require__(62);
var core_1 = __webpack_require__(0);
function getDefaultMargin(halign, valign, isTreeMap, form) {
    switch (valign) {
        case "middle": {
            switch (halign) {
                case "right":
                    return isTreeMap ? 20 : 60;
                case "left":
                    return 120;
                case "center":
                    throw new Error("cant place legend on center, middle");
            }
        }
        // eslint-disable-next-line no-fallthrough
        case "top":
            return 20;
        case "bottom":
            return isTreeMap || form === "line" ? 20 : 60;
    }
}
var Legend = /** @class */ (function () {
    function Legend(_data, config, _events) {
        var _this = this;
        this._data = _data;
        this._events = _events;
        var defaults = {
            form: "rect",
            itemPadding: 20,
            halign: "right",
            valign: "top",
            direction: "row",
            type: "groupName",
        };
        this.config = __assign(__assign({}, defaults), config);
        this.config.margin =
            config.margin ||
                getDefaultMargin(this.config.halign, this.config.valign, (0, ts_data_1.isTreeCollection)(this._data), this.config.form);
        this._handlers = {
            onclick: function (id, pieLike) {
                return _this._events.fire(types_1.ChartEvents.toggleSeries, [id, pieLike]);
            },
            onkeyup: function (id, pieLike, event) {
                // FIXME: set handler correctly according app code style
                event.preventDefault();
                // enter or space
                if (event.key === "Enter" || event.key === " ") {
                    _this._events.fire(types_1.ChartEvents.toggleSeries, [id, pieLike]);
                }
            },
        };
    }
    Legend.prototype.scaleReady = function (sizes) {
        var isColumnInCorner = this.config.direction === "column" &&
            (this.config.halign === "left" || this.config.halign === "right");
        switch (this.config.valign) {
            case "middle":
                if (this.config.halign === "right") {
                    sizes.right += this.config.size >= 0 ? this.config.size : 200;
                }
                else if (this.config.halign === "left") {
                    sizes.left += this.config.size >= 0 ? this.config.size : 200;
                }
                break;
            case "top":
                if (isColumnInCorner) {
                    sizes[this.config.halign] += this.config.size >= 0 ? this.config.size : 200;
                }
                else {
                    sizes.top += this.config.size >= 0 ? this.config.size : 40;
                }
                break;
            case "bottom":
                if (isColumnInCorner) {
                    sizes[this.config.halign] += this.config.size >= 0 ? this.config.size : 200;
                }
                else {
                    sizes.bottom += this.config.size >= 0 ? this.config.size : 40;
                }
                break;
        }
    };
    Legend.prototype.paint = function (width, height) {
        var _this = this;
        var getLegendAriaAttrs = function (item) { return ({
            role: "button",
            "aria-label": item.active ? "Hide chart ".concat(item.text) : "Show chart ".concat(item.text),
        }); };
        var data = this._getData();
        var config = this.config;
        config.$sizes = {
            width: 0,
            height: 0,
        };
        var positionX;
        var positionY;
        var font = (0, common_1.getFontStyle)("legend-text");
        var figureWidth = 10; // get Figure width from config ??
        var lineWidth = 500; // get Figure width from config ??
        var margin = config.margin, itemPadding = config.itemPadding;
        var svg = [];
        var isMid = config.valign === "middle";
        var xPadding = 0;
        var yPadding = 0;
        var xPaddingMax = 0;
        var yPaddingMax = 0;
        data.forEach(function (item, index) {
            if (!isMid && config.direction === "row") {
                var textWidth = (0, common_1.getTextWidth)(item.text, font);
                if (xPadding + textWidth + figureWidth * 1.5 > width && index !== 0) {
                    xPadding = 0;
                    yPadding += itemPadding + 2;
                }
            }
            svg.push((0, dom_1.sv)("g", __assign(__assign({ transform: "translate(".concat(xPadding, ",").concat(yPadding, ")"), onclick: [_this._handlers.onclick, item.id, _this.config.values], onkeyup: [_this._handlers.onkeyup, item.id, _this.config.values], class: "legend-item ".concat(!item.active ? "not-active" : "") }, getLegendAriaAttrs(item)), { tabindex: 0 }), [
                (0, dom_1.sv)("text", {
                    x: item.maxValue ? 0 : figureWidth / 2 + 5,
                    y: 0,
                    class: "start-text legend-text",
                }, [(0, common_1.verticalCenteredText)(item.text)]),
                [
                    (0, legend_1.legendShape)(config.form, item),
                    !isNaN(item.maxValue) && !isNaN(item.minValue) && (0, legend_1.legendTicks)(config.form, item),
                ],
            ]));
            var itemWidth = config.form !== "line" ? figureWidth * 1.5 + (0, common_1.getTextWidth)(item.text, font) : 0;
            if (!isMid && config.direction === "row") {
                xPadding += itemWidth + itemPadding;
                xPaddingMax = xPaddingMax > xPadding ? xPaddingMax : xPadding;
            }
            else {
                xPadding = 0;
                xPaddingMax = xPaddingMax > itemWidth ? xPaddingMax : itemWidth;
                yPadding += itemPadding + 2;
                yPaddingMax = yPaddingMax > yPadding ? yPaddingMax : yPadding;
            }
        });
        switch (config.valign) {
            case "top":
                if (config.direction === "row") {
                    positionY = -margin - yPadding - figureWidth / 2 - (config.form === "line" ? 15 : 0);
                }
                else {
                    positionY =
                        config.halign === "center"
                            ? -yPaddingMax + figureWidth / 2
                            : margin + figureWidth / 2;
                }
                break;
            case "middle":
                positionY = (height - yPaddingMax) / 2 + itemPadding / 2;
                break;
            case "bottom":
                if (config.direction === "row") {
                    positionY = height + (config.form !== "line" ? margin : 10);
                }
                else {
                    positionY =
                        height + figureWidth - (config.halign === "center" ? 0 : margin + yPaddingMax);
                }
                break;
        }
        switch (config.halign) {
            case "left":
                positionX = isMid
                    ? -xPaddingMax
                    : figureWidth / 2 - (config.direction === "row" ? 0 : config.size || 200);
                break;
            case "center":
                positionX = (width - xPaddingMax - (config.form === "line" ? lineWidth : 0)) / 2;
                break;
            case "right":
                if (isMid) {
                    positionX = width + margin + figureWidth / 2;
                }
                else if (config.direction === "row") {
                    positionX =
                        width -
                            xPaddingMax +
                            itemPadding +
                            (config.form === "line" ? -lineWidth - margin : figureWidth / 2);
                }
                else {
                    positionX = width + margin + figureWidth / 2;
                }
                break;
        }
        return (0, dom_1.sv)("g", {
            transform: "translate(".concat(positionX > 0 ? positionX : 0, ", ").concat(positionY, ")"),
            "aria-label": "Legend",
            tabindex: 0,
        }, svg);
    };
    Legend.prototype._getData = function () {
        var drawData = [];
        if (this.config.type === "scale") {
            var _a = this.config.values, color = _a.color, positiveColor = _a.positiveColor, negativeColor = _a.negativeColor, text = _a.text, minValue = _a.minValue, maxValue = _a.maxValue, tick = _a.tick, majorTick = _a.majorTick, step = _a.step, tickTemplate = _a.tickTemplate;
            var _b = this.setCriticals(), minValueCalc = _b[0], maxValueCalc = _b[1];
            drawData.push({
                id: (0, core_1.uid)(),
                text: text,
                alpha: 1,
                fill: color,
                minValue: minValue !== null && minValue !== void 0 ? minValue : minValueCalc,
                maxValue: maxValue !== null && maxValue !== void 0 ? maxValue : maxValueCalc,
                positiveColor: positiveColor,
                negativeColor: negativeColor,
                active: true,
                tick: tick,
                majorTick: majorTick,
                step: step,
                tickTemplate: tickTemplate,
            });
        }
        else if (this.config.values) {
            var text_1 = (0, common_1.locator)(this.config.values.text);
            var fill_1 = (0, common_1.locator)(this.config.values.color);
            this._data.map(function (item, index) {
                drawData.push({
                    id: item.id,
                    text: text_1(item).toString(),
                    alpha: 1,
                    fill: fill_1(item).toString() || (0, common_1.getDefaultColor)(index),
                    active: !item.$hidden,
                });
            });
        }
        else if (this.config.treeSeries) {
            var series = this.config.treeSeries;
            if (this.config.type === "groupName") {
                series.forEach(function (serie) {
                    drawData.push({
                        id: serie.id,
                        text: serie.name || serie.id,
                        alpha: 1,
                        fill: serie.color,
                        active: serie.active,
                    });
                });
            }
            else {
                var getText_1 = function (serie) {
                    if (serie.from && serie.to)
                        return "".concat(serie.from, " - ").concat(serie.to);
                    if (serie.less)
                        return "<= ".concat(serie.less);
                    if (serie.greater)
                        return ">= ".concat(serie.greater);
                };
                series.forEach(function (serie) {
                    drawData.push({
                        id: serie.id,
                        text: getText_1(serie),
                        alpha: 1,
                        fill: serie.color,
                        active: serie.active,
                    });
                });
            }
        }
        else {
            var series = this.config.$seriesInfo;
            for (var _i = 0, _c = series; _i < _c.length; _i++) {
                var serie = _c[_i];
                var _d = serie.config, fill = _d.fill, color = _d.color, id = _d.id, alpha = _d.alpha, active = _d.active, label = _d.label, value = _d.value;
                var useColor = fill && color;
                var text = label && typeof label === "function" ? label(serie.config) : label || value;
                drawData.push({
                    id: id,
                    text: text,
                    fill: fill || color,
                    color: useColor && color,
                    active: active,
                    alpha: alpha,
                });
            }
        }
        return drawData;
    };
    Legend.prototype.setCriticals = function () {
        var value = this.config.values.value;
        var min;
        var max;
        this._data.forEach(function (item) {
            if (item[value]) {
                min = !min || item[value] < min ? item[value] : min;
                max = !max || item[value] > max ? item[value] : max;
            }
        });
        max = Math.ceil(max / 10) * 10;
        min = Math.floor(min / 10) * 10;
        if (min === max)
            min -= 10;
        return [min, max];
    };
    return Legend;
}());
exports.Legend = Legend;


/***/ }),
/* 62 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.legendTicks = exports.legendShape = void 0;
var core_1 = __webpack_require__(0);
var dom_1 = __webpack_require__(1);
var figureSize = 10;
var lineHeight = 16;
var scaleLen = 500;
var forms = {
    circle: function (color, fill, fillOpacity) {
        return (0, dom_1.sv)("circle", {
            id: (0, core_1.uid)(),
            r: figureSize / 2,
            fill: fill,
            class: "figure ".concat(color !== "none" ? "with-stroke" : ""),
            "stroke-width": 2,
            "fill-opacity": fillOpacity,
            stroke: color,
            transform: "translate(0, -1)",
        });
    },
    rect: function (color, fill, fillOpacity) {
        return (0, dom_1.sv)("rect", {
            id: (0, core_1.uid)(),
            fill: fill,
            "fill-opacity": fillOpacity,
            width: figureSize,
            "stroke-width": 2,
            height: figureSize,
            class: "figure ".concat(color !== "none" ? "with-stroke" : ""),
            stroke: color,
            transform: "translate(".concat(-figureSize / 2, ", ").concat(-figureSize / 2, ")"),
        });
    },
    line: function (color, fill, fillOpacity, middle) {
        var getStopPoint = function (color, percent, value) {
            return (0, dom_1.sv)("stop", { offset: "".concat(percent, "%"), "stop-color": color, class: color ? "" : value });
        };
        var linearId = "lg" + (0, core_1.uid)();
        var gradientFill = (0, dom_1.sv)("linearGradient", {
            id: linearId,
        }, [
            getStopPoint(color[1], 0, "heat-negative"),
            getStopPoint(fill, middle, "heat-neutral"),
            getStopPoint(color[0], 100, "heat-positive"),
        ]);
        var line = (0, dom_1.sv)("rect", {
            id: (0, core_1.uid)(),
            y: lineHeight,
            fill: "url(#".concat(linearId, ")"),
            "fill-opacity": fillOpacity,
            width: scaleLen,
            "stroke-width": 2,
            height: lineHeight,
            class: "figure figure-line",
            stroke: color,
        });
        return (0, dom_1.sv)("g", {
            id: (0, core_1.uid)(),
            width: scaleLen,
            height: lineHeight,
        }, [gradientFill, line]);
    },
};
function legendShape(form, item) {
    var legendForm;
    if (typeof form === "string") {
        legendForm = forms[form];
    }
    return legendForm(form === "line" ? [item.positiveColor, item.negativeColor] : item.color || "none", item.fill, item.alpha || 1, form === "line" && item.minValue < 0
        ? (Math.abs(item.minValue) * 100) / (item.maxValue - item.minValue)
        : 0);
}
exports.legendShape = legendShape;
function legendTicks(form, item) {
    if (form !== "line" || isNaN(item.maxValue) || isNaN(item.minValue)) {
        return;
    }
    var maxValue = item.maxValue, minValue = item.minValue, step = item.step, tick = item.tick, majorTick = item.majorTick, tickTemplate = item.tickTemplate;
    var tickValue = tick || 10;
    var ticksCount = (maxValue - minValue) / tickValue;
    var ticks = [];
    for (var index = 0; index <= Math.ceil(ticksCount / (step || 1)); index++) {
        var value = index === 0 ? minValue : (minValue + tickValue * (step || 1) * index).toFixed();
        ticks.push(value > maxValue ? maxValue : value);
    }
    ticks = ticks.map(function (tick, index, arr) {
        var text = (index % (majorTick || 1) === 0 || index === arr.length - 1) &&
            (0, dom_1.sv)("text", {
                x: index === arr.length - 1
                    ? scaleLen - 0.5
                    : ((index * scaleLen) / ticksCount) * (step || 1) - 0.5,
                y: lineHeight * 2 + 2,
            }, tickTemplate ? tickTemplate(tick) : tick);
        var degree = (0, dom_1.sv)("rect", {
            x: index === arr.length - 1
                ? scaleLen - 0.5
                : ((index * scaleLen) / ticksCount) * (step || 1) - 0.5,
            y: lineHeight + 2,
            width: 1,
            height: 4,
        });
        return (0, dom_1.sv)("g", {
            transform: "translate(0, ".concat(lineHeight, ")"),
            class: "legend-tick",
        }, [text, degree]);
    });
    return ticks;
}
exports.legendTicks = legendTicks;


/***/ }),
/* 63 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
var RadialScale_1 = __webpack_require__(64);
var Scale_1 = __webpack_require__(16);
var TextScale_1 = __webpack_require__(66);
var scaleTypes = {
    radial: RadialScale_1.RadialScale,
    text: TextScale_1.TextScale,
    numeric: Scale_1.Scale,
};
exports.default = scaleTypes;


/***/ }),
/* 64 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
Object.defineProperty(exports, "__esModule", { value: true });
exports.RadialScale = void 0;
var circle_1 = __webpack_require__(10);
var Scale_1 = __webpack_require__(16);
var RadialScale = /** @class */ (function (_super) {
    __extends(RadialScale, _super);
    function RadialScale(_data, config) {
        return _super.call(this, _data, config, "radial") || this;
    }
    RadialScale.prototype.paint = function (width, height) {
        var _this = this;
        if (this.config.hidden) {
            return null;
        }
        var zebra = this.config.zebra;
        var attribute = this.config.value;
        var realAxis = this.config.showAxis ? this._axis.steps : null;
        var axis = this._axis.steps.map(function (step) { return _this.point(step); });
        var scales = this._data.map(function (item) { return item[attribute]; });
        var config = { scales: scales, axis: axis, realAxis: realAxis, zebra: zebra, attribute: attribute };
        return (0, circle_1.radarScale)(config, width, height);
    };
    RadialScale.prototype.point = function (val) {
        return (val - this._axis.min) / (this._axis.max - this._axis.min);
    };
    return RadialScale;
}(Scale_1.Scale));
exports.RadialScale = RadialScale;


/***/ }),
/* 65 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.AxisCreator = void 0;
var common_1 = __webpack_require__(2);
var core_1 = __webpack_require__(0);
var allowedBases = [1, 2, 3, 5, 10];
var AxisCreator = /** @class */ (function () {
    function AxisCreator(_data, conf) {
        if (conf === void 0) { conf = {}; }
        this._data = _data;
        var chekedData = [];
        for (var _i = 0, _a = this._data; _i < _a.length; _i++) {
            var item = _a[_i];
            if (typeof item === "number") {
                chekedData.push(item);
            }
            if (typeof item === "string" && !isNaN(parseFloat(item))) {
                chekedData.push(parseFloat(item));
            }
        }
        var defaults = {
            min: Math.min.apply(Math, chekedData),
            max: Math.max.apply(Math, chekedData),
            maxTicks: this._data.length < 20 ? this._data.length : 20,
        };
        this.config = __assign(__assign({}, defaults), conf);
        if (this.config.type !== "radial" && !(0, core_1.isDefined)(conf.min)) {
            var min = this.config.min;
            var newMin = min - this._getStep();
            if ((min > 0 && newMin >= 0) || min < 0) {
                this.config.min = newMin;
            }
        }
        if (this.config.padding) {
            this._addPadding();
        }
    }
    AxisCreator.prototype.getScale = function () {
        var steps = this.config.log ? this._logSteps() : this._calculateSteps(this._getStep());
        return {
            min: steps[0],
            max: steps[steps.length - 1],
            steps: steps,
        };
    };
    AxisCreator.prototype._getStep = function () {
        var ticks = this.config.maxTicks;
        var dif = this.config.max - this.config.min;
        var exponent = Math.floor((0, common_1.log10)(dif / ticks));
        var step = Math.pow(10, exponent) || 1;
        var rawBase = dif / step / ticks;
        var nearestBase = allowedBases[__spreadArray(__spreadArray([], allowedBases, true), [rawBase], false).sort(function (a, b) { return a - b; }).indexOf(rawBase)];
        return nearestBase * step;
    };
    AxisCreator.prototype._calculateSteps = function (step) {
        var firstIndex = Math.floor(this.config.min / step);
        var lastIndex = Math.ceil(this.config.max / step);
        var steps = [];
        for (var i = this.config.type === "radial" ? firstIndex - 1 : firstIndex; i <= lastIndex; i++) {
            var currentStep = step * i;
            if (Math.floor(currentStep) !== currentStep) {
                currentStep = parseFloat(currentStep.toFixed(8));
            }
            steps.push(currentStep);
        }
        return steps;
    };
    AxisCreator.prototype._logSteps = function () {
        var steps = [];
        if (this.config.min < 0) {
            var negativeExponent = Math.ceil((0, common_1.log10)(-this.config.min));
            for (var i = negativeExponent; i > 0; i--) {
                steps.push(-(Math.pow(10, i)));
            }
            steps.push(0);
        }
        if (this.config.max > 0) {
            var positiveExponent = Math.ceil((0, common_1.log10)(this.config.max));
            for (var i = 0; i <= positiveExponent; i++) {
                steps.push(Math.pow(10, i));
            }
        }
        return steps;
    };
    AxisCreator.prototype._addPadding = function () {
        this.config.min -= this.config.padding;
    };
    return AxisCreator;
}());
exports.AxisCreator = AxisCreator;


/***/ }),
/* 66 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.TextScale = void 0;
var common_1 = __webpack_require__(2);
var Scale_1 = __webpack_require__(16);
var SvgScales_1 = __webpack_require__(26);
var renderScale = {
    left: SvgScales_1.left,
    right: SvgScales_1.right,
    bottom: SvgScales_1.bottom,
    top: SvgScales_1.top,
};
var renderGrid = {
    left: SvgScales_1.leftGrid,
    right: SvgScales_1.rightGrid,
    bottom: SvgScales_1.bottomGrid,
    top: SvgScales_1.topGrid,
};
var TextScale = /** @class */ (function (_super) {
    __extends(TextScale, _super);
    function TextScale() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    TextScale.prototype.scaleReady = function (sizes) {
        var max = this._data.getLength() - 1;
        var steps = this._data.map(this.locator);
        this._axis = { max: max, steps: steps };
        sizes[this._position] += this.config.size;
    };
    TextScale.prototype.point = function (value) {
        var pos = this._axis.steps.indexOf(value);
        if (this._padding) {
            var max = this._axis.max + 1;
            var padding = 0.5 / max;
            var point = pos / max;
            return this._isXDirection ? padding + point : 1 - padding - point;
        }
        else {
            return this._isXDirection ? pos / this._axis.max : 1 - pos / this._axis.max;
        }
    };
    TextScale.prototype.paint = function (width, height) {
        var _this = this;
        if (this.config.hidden) {
            return null;
        }
        var points = this._axis.steps.map(function (item, index) { return [
            _this._isXDirection ? _this._getAxisPoint(index) * width : _this.point(item) * height,
            item,
        ]; });
        return renderScale[this._position](points, this.config, width, height);
    };
    TextScale.prototype.scaleGrid = function () {
        var _this = this;
        var getPoints = function (width, height) {
            return _this._axis.steps.map(function (item, index) { return [
                _this._isXDirection ? _this._getAxisPoint(index) * width : _this._getAxisPoint(index) * height,
                item,
            ]; });
        };
        var type = this._position;
        var grid = this.config.grid;
        var dashed = this.config.dashed;
        var hidden = this.config.hidden;
        var getSpecificLevel = function () { return _this._axis.steps.indexOf(_this.config.targetLine); };
        return {
            paint: function (width, height) {
                var targetLine = getSpecificLevel();
                var points = getPoints(width, height);
                var config = { targetLine: targetLine, dashed: dashed, grid: grid, hidden: hidden };
                return renderGrid[type](points, width, height, config);
            },
        };
    };
    TextScale.prototype._setDefaults = function (config) {
        var defaults = {
            scalePadding: 30,
            textPadding: 12,
            grid: true,
            targetLine: null,
            showText: true,
        };
        this.locator = (0, common_1.locator)(config.text);
        this.config = __assign(__assign({}, defaults), config);
    };
    TextScale.prototype._getAxisPoint = function (index) {
        var max = this._axis.max;
        if (this._padding) {
            var count = max + 1;
            var padding = 0.5 / count;
            var point = index / count;
            return this._isXDirection ? padding + point : 1 - padding - point;
        }
        else {
            return this._isXDirection ? index / max : 1 - index / max;
        }
    };
    return TextScale;
}(Scale_1.Scale));
exports.TextScale = TextScale;


/***/ }),
/* 67 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
var Area_1 = __webpack_require__(27);
var Bar_1 = __webpack_require__(30);
var BarX_1 = __webpack_require__(68);
var Donut_1 = __webpack_require__(69);
var Line_1 = __webpack_require__(19);
var Pie_1 = __webpack_require__(70);
var Pie3D_1 = __webpack_require__(71);
var Radar_1 = __webpack_require__(72);
var Scatter_1 = __webpack_require__(73);
var Spline_1 = __webpack_require__(74);
var SplineArea_1 = __webpack_require__(75);
var TreeMap_1 = __webpack_require__(76);
var CalendarHeatMap_1 = __webpack_require__(77);
var seriesTypes = {
    line: Line_1.default,
    spline: Spline_1.default,
    area: Area_1.default,
    splineArea: SplineArea_1.default,
    scatter: Scatter_1.default,
    pie: Pie_1.default,
    pie3D: Pie3D_1.default,
    donut: Donut_1.default,
    radar: Radar_1.default,
    bar: Bar_1.default,
    xbar: BarX_1.default,
    treeMap: TreeMap_1.default,
    calendarHeatMap: CalendarHeatMap_1.default,
};
exports.default = seriesTypes;


/***/ }),
/* 68 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
Object.defineProperty(exports, "__esModule", { value: true });
var Bar_1 = __webpack_require__(30);
var BarX = /** @class */ (function (_super) {
    __extends(BarX, _super);
    function BarX() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    BarX.prototype.addScale = function (type, scale) {
        var realtype = type === "top" || type === "bottom" ? "left" : "top";
        _super.prototype.addScale.call(this, realtype, scale);
    };
    BarX.prototype.paint = function (width, height, prev) {
        return _super.prototype.paint.call(this, height, width, prev);
    };
    BarX.prototype.getTooltipType = function (id, x, y) {
        if (this.config.baseLine !== undefined && this._baseLinePosition > x) {
            return "left";
        }
        return "right";
    };
    BarX.prototype.getClosest = function (x, y) {
        var res = [Infinity, null, null, null];
        for (var _i = 0, _a = this._points; _i < _a.length; _i++) {
            var point = _a[_i];
            var dist = this._getClosestDist(x, y, point[1], point[0]);
            if (res[0] > dist) {
                res[0] = dist;
                res[1] = point[1];
                res[2] = point[0];
                res[3] = point[2];
            }
        }
        return res;
    };
    BarX.prototype._getText = function (item) {
        return item[4].toString();
    };
    BarX.prototype._getClosestDist = function (x, y, px, py) {
        if (this.config.stacked && x > px) {
            return Infinity;
        }
        return Math.abs(y - py);
    };
    BarX.prototype._path = function (item, prev) {
        item[0] += this._shift;
        return "\nM ".concat(prev, " ").concat(item[0] - this.config.barWidth / 2, "\nH ").concat(item[1], "\nv ").concat(this.config.barWidth, "\nH ").concat(prev);
    };
    BarX.prototype._base = function (height) {
        var baseLine = this.config.baseLine;
        return (this._baseLinePosition = baseLine !== undefined ? this.yScale.point(baseLine) * height : 0);
    };
    BarX.prototype._text = function (item, prev, rotate) {
        var x = (prev + item[1]) / 2;
        var y = item[0];
        var canRotate = rotate && !isNaN(rotate);
        return {
            x: x,
            y: y,
            class: "bar-text",
            transform: canRotate ? "rotate(".concat(rotate, " ").concat(x, " ").concat(y, ")") : "",
        };
    };
    return BarX;
}(Bar_1.default));
exports.default = BarX;


/***/ }),
/* 69 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
var dom_1 = __webpack_require__(1);
var circle_1 = __webpack_require__(10);
var common_1 = __webpack_require__(2);
var NoScaleSeria_1 = __webpack_require__(18);
var Donut = /** @class */ (function (_super) {
    __extends(Donut, _super);
    function Donut() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    Donut.prototype.paint = function (width, height) {
        var _this = this;
        var getPointAriaAttrs = function (value, text, percent) {
            if (percent === void 0) { percent = 0; }
            return ({
                role: "graphics-symbol",
                "aria-roledescription": "piece of donut",
                "aria-label": "".concat(text || "", ", ").concat(value, " (").concat((0, common_1.roundToTwoNumAfterPoint)(percent), "%)"),
            });
        };
        var getChartAriaAttrs = function (cfg) { return ({
            "aria-label": "chart ".concat(cfg.text || ""),
        }); };
        var _a = this.config, stroke = _a.stroke, strokeWidth = _a.strokeWidth, useLines = _a.useLines, subType = _a.subType, valueTemplate = _a.valueTemplate;
        var defaultStrokeWidth = !strokeWidth || strokeWidth < 1 ? 4 : strokeWidth > 15 ? 15 : strokeWidth;
        var radius;
        if (height > width) {
            radius = width / 2;
        }
        else {
            radius = height / 2;
        }
        var currentPercent = -0.25;
        var svg = [];
        var lines = [];
        var textPoints = [];
        var tooltipData = [];
        if (this._points.length > 1 && stroke) {
            svg.push((0, dom_1.sv)("circle", {
                cx: 0,
                cy: 0,
                r: radius - 0.5,
                fill: stroke,
            }));
        }
        this._points.forEach(function (item, index) {
            var _a, _b, _c;
            var link = [];
            var percent = item[0], value = item[1], id = item[2], text = item[3], color = item[4];
            var err = percent === 0 || percent === 1 ? -0.000001 : 0;
            var _d = (0, circle_1.getCoordinates)(currentPercent, radius, radius, _this._points.length > 1 && stroke ? defaultStrokeWidth / 2 : null), startX = _d[0], startY = _d[1];
            var avPercent = currentPercent + percent / 2;
            currentPercent += percent + err;
            var _e = (0, circle_1.getCoordinates)(currentPercent, radius, radius, _this._points.length > 1 && stroke ? -defaultStrokeWidth / 2 : null), endX = _e[0], endY = _e[1];
            var largeArcFlag = percent > 0.5 ? 1 : 0;
            var middleLine = (0, circle_1.getCoordinates)(avPercent, radius, radius);
            var isRight = avPercent > -0.25 && avPercent < 0.25;
            var isUp = avPercent > 0.5 || avPercent < 0;
            var lineLength = avPercent < 0.25 ? 5 : -5;
            var _f = [5, 30], startPart = _f[0], endPart = _f[1];
            var _g = [
                (0, circle_1.getCoordinates)(avPercent, radius + startPart, radius + startPart),
                (0, circle_1.getCoordinates)(avPercent, radius + endPart, radius + endPart),
            ], linkStart = _g[0], linkEnd = _g[1];
            var totalValue = typeof valueTemplate === "function"
                ? valueTemplate(percent)
                : Math.round(percent * 100) + "%";
            switch (_this.config.subType) {
                case "basic": {
                    var className = isRight ? "donut-value-title start-text" : "donut-value-title end-text";
                    var textPadding = 10;
                    var dy = isUp ? -textPadding * 2 : textPadding;
                    var linkStart_1 = (0, circle_1.getCoordinates)(avPercent, radius + 10, radius + 10);
                    var className2 = isRight ? "donut-value start-text" : "donut-value end-text";
                    var currentText_1 = {
                        text1: {
                            x: useLines ? linkEnd[0] : linkStart_1[0],
                            y: (useLines ? linkEnd[1] : linkStart_1[1]) + dy,
                            width: 0,
                            height: 0,
                            class: "",
                        },
                        text2: {
                            x: useLines ? linkEnd[0] : linkStart_1[0],
                            y: (useLines ? linkEnd[1] : linkStart_1[1]) + dy + 16,
                            width: 0,
                            height: 0,
                            class: "",
                        },
                        changeSector: false,
                        line: lineLength,
                        right: isRight,
                        dy: dy,
                    };
                    var text1 = (0, dom_1.sv)("text", __assign(__assign({ x: useLines ? linkEnd[0] : linkStart_1[0], y: (useLines ? linkEnd[1] : linkStart_1[1]) + dy, class: className }, getPointAriaAttrs(value, text, percent)), { tabindex: 0 }), [(0, common_1.verticalCenteredText)(text.toString())]);
                    var text2 = (0, dom_1.sv)("text", {
                        x: useLines ? linkEnd[0] : linkStart_1[0],
                        y: (useLines ? linkEnd[1] : linkStart_1[1]) + dy + 16,
                        class: className2,
                        "aria-hidden": "true",
                    }, [(0, common_1.verticalCenteredText)(value.toString())]);
                    _a = (0, common_1.getSizesSVGText)(text.toString(), {
                        font: "normal 14px Roboto",
                        lineHeight: 14,
                    }), currentText_1.text1.width = _a[0], currentText_1.text1.height = _a[1];
                    _b = (0, common_1.getSizesSVGText)(value.toString(), {
                        font: "normal 12px Roboto",
                        lineHeight: 12,
                    }), currentText_1.text2.width = _b[0], currentText_1.text2.height = _b[1];
                    var textRadius_1 = useLines ? radius + endPart : radius + 10;
                    if (textPoints.length !== 0) {
                        if (isRight) {
                            textPoints.forEach(function (previosText) {
                                (0, common_1.checkPositions)(currentText_1.text1, previosText.text1, textRadius_1, textRadius_1, currentText_1);
                                (0, common_1.checkPositions)(currentText_1.text1, previosText.text2, textRadius_1, textRadius_1, currentText_1);
                            });
                            if (currentText_1.text1.class)
                                currentText_1.text2.class = currentText_1.text1.class;
                        }
                        else {
                            textPoints.forEach(function (previosText) {
                                (0, common_1.checkPositions)(currentText_1.text2, previosText.text2, textRadius_1, textRadius_1, currentText_1);
                                (0, common_1.checkPositions)(currentText_1.text2, previosText.text1, textRadius_1, textRadius_1, currentText_1);
                            });
                            if (currentText_1.text2.class)
                                currentText_1.text1.class = currentText_1.text2.class;
                        }
                        text1.attrs.x = currentText_1.text1.x;
                        text1.attrs.y = currentText_1.text1.y;
                        text2.attrs.x = currentText_1.text2.x;
                        text2.attrs.y = currentText_1.text2.y;
                        if (currentText_1.text1.class || currentText_1.text2.class) {
                            text1.attrs.class = currentText_1.text1.class;
                            text2.attrs.class = currentText_1.text2.class;
                        }
                        lineLength = currentText_1.line;
                        if (useLines) {
                            linkEnd[0] = currentText_1.text1.x;
                            linkEnd[1] = currentText_1.text1.y - dy;
                        }
                        else {
                            linkStart_1[0] = currentText_1.text1.x;
                            linkStart_1[1] = currentText_1.text1.y - dy;
                        }
                    }
                    textPoints.push(currentText_1);
                    var text3 = (0, dom_1.sv)("text", __assign({ x: (middleLine[0] * 7) / 9, y: (middleLine[1] * 7) / 9, class: "pie-inner-value" }, getPointAriaAttrs(value, text, percent)), [(0, common_1.verticalCenteredText)(totalValue)]);
                    link.push((0, dom_1.sv)("g", {
                        id: "".concat(id, "-text"),
                        class: "chart donut",
                    }, [text1, text2]));
                    link.push(text3);
                    break;
                }
                case "valueOnly": {
                    var valueText = (0, dom_1.sv)("text", __assign(__assign({ x: (middleLine[0] * 7) / 9, y: (middleLine[1] * 7) / 9, class: "pie-inner-value" }, getPointAriaAttrs(value, text, percent)), { tabindex: 0 }), [(0, common_1.verticalCenteredText)("".concat(value))]);
                    link.push(valueText);
                    break;
                }
                case "percentOnly": {
                    var percentText = (0, dom_1.sv)("text", __assign(__assign({ x: (middleLine[0] * 7) / 9, y: (middleLine[1] * 7) / 9, class: "pie-inner-value" }, getPointAriaAttrs(value, text, percent)), { tabindex: 0 }), [(0, common_1.verticalCenteredText)(totalValue)]);
                    link.push(percentText);
                    break;
                }
            }
            if (useLines && subType === "basic") {
                var dy = isUp ? 3 : 0;
                link.push((0, dom_1.sv)("path", {
                    d: "M".concat(linkStart[0], " ").concat(linkStart[1], " L").concat(linkEnd[0], "\n\t\t\t\t\t\t\t").concat(linkEnd[1] + dy, " h ").concat(lineLength),
                    id: "".concat(id, "-connector"),
                    class: "pie-value-connector chart donut",
                }));
            }
            var centerX = 0, centerY = 0;
            var pseudoRadius = defaultStrokeWidth / (2 * Math.sin(Math.PI / _this._points.length));
            if (_this._points.length > 1 && stroke) {
                _c = (0, circle_1.getCoordinates)(avPercent, pseudoRadius, pseudoRadius), centerX = _c[0], centerY = _c[1];
            }
            var d = "M ".concat(startX, " ").concat(startY, " A ").concat(radius, " ").concat(radius, " 0 ").concat(largeArcFlag, " 1 ").concat(endX, " ").concat(endY, "\n\t\t\t\tL ").concat(0 + centerX, " ").concat(0 + centerY);
            var _h = (0, circle_1.getCoordinates)(avPercent, 4, 4), shiftX = _h[0], shiftY = _h[1];
            var currentSector = (0, dom_1.sv)("path", {
                d: d,
                _key: id,
                fill: color || (0, common_1.getDefaultColor)(index),
                class: "chart donut",
                onclick: [_this._handlers.onclick, item[1], item[2]],
                // eslint-disable-next-line @typescript-eslint/unbound-method
                onmouseout: [circle_1.pieLikeHandlers.onmouseout],
                // eslint-disable-next-line @typescript-eslint/unbound-method
                onmouseover: [circle_1.pieLikeHandlers.onmouseover, shiftX, shiftY],
                role: "presentation",
            });
            link.unshift(currentSector);
            svg.push((0, dom_1.sv)("g", { id: id }, link));
            if (_this._points.length === 1) {
                tooltipData.push([width / 2, height / 2]);
            }
            else {
                tooltipData.push([middleLine[0] * 0.8 + width / 2, middleLine[1] * 0.8 + height / 2]);
            }
        });
        this._center = [width / 2, height / 2];
        this._tooltipData = tooltipData;
        svg = svg.concat(lines);
        svg.push((0, dom_1.sv)("circle", {
            cx: 0,
            cy: 0,
            r: (radius * 5) / 9,
            fill: "#FFFFFF",
            role: "presentation",
        }));
        return (0, dom_1.sv)("g", __assign(__assign({ transform: "translate(".concat(width / 2, ", ").concat(height / 2, ")") }, getChartAriaAttrs(this.config)), { tabindex: 0 }), svg);
    };
    return Donut;
}(NoScaleSeria_1.default));
exports.default = Donut;


/***/ }),
/* 70 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
var core_1 = __webpack_require__(0);
var dom_1 = __webpack_require__(1);
var circle_1 = __webpack_require__(10);
var common_1 = __webpack_require__(2);
var NoScaleSeria_1 = __webpack_require__(18);
var Pie = /** @class */ (function (_super) {
    __extends(Pie, _super);
    function Pie() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    // todo move to NoScaleSeria
    Pie.prototype.paint = function (width, height) {
        var _this = this;
        var getPointAriaAttrs = function (value, text, percent) { return ({
            role: "graphics-symbol",
            "aria-roledescription": "piece of pie",
            "aria-label": "".concat(text || "", ", ").concat(value, " (").concat((0, common_1.roundToTwoNumAfterPoint)(percent), "%)"),
        }); };
        var getChartAriaAttrs = function (cfg) { return ({
            "aria-label": "chart ".concat(cfg.text || ""),
        }); };
        var _a = this.config, stroke = _a.stroke, strokeWidth = _a.strokeWidth, gradient = _a.gradient, useLines = _a.useLines, showText = _a.showText, showTextTemplate = _a.showTextTemplate, subType = _a.subType, valueTemplate = _a.valueTemplate;
        var defaultStrokeWidth = !strokeWidth || strokeWidth < 1 ? 4 : strokeWidth > 15 ? 15 : strokeWidth;
        var radius;
        if (height > width) {
            radius = width / 2;
        }
        else {
            radius = height / 2;
        }
        var currentPercent = -0.25; // 0 percent is (1, 0) point, -.25 is (0, -1) point
        var textPoints = [];
        var tooltipData = [];
        var svg = [];
        var defs = [];
        var pie = [];
        var lines = [];
        if (this._points.length > 1 && stroke) {
            svg.push((0, dom_1.sv)("circle", {
                cx: 0,
                cy: 0,
                r: radius - 0.5,
                fill: stroke,
            }));
        }
        this._points.forEach(function (item, index) {
            var _a, _b, _c;
            var link = [];
            var percent = item[0], value = item[1], id = item[2], text = item[3], color = item[4];
            var err = percent === 0 || percent === 1 ? -0.000001 : 0;
            var fill = color || (0, common_1.getDefaultColor)(index);
            if (gradient) {
                var grad = gradient(color);
                var gradientId = "gradient" + (0, core_1.uid)();
                var radialGradient = (0, common_1.getRadialGradient)(grad.options, grad.stops, gradientId);
                fill = "url(#".concat(gradientId, ")");
                defs.push(radialGradient);
            }
            var _d = (0, circle_1.getCoordinates)(currentPercent, radius, radius, _this._points.length > 1 && stroke ? defaultStrokeWidth / 2 : null), startX = _d[0], startY = _d[1];
            var avPercent = currentPercent + percent / 2;
            var lineLength = avPercent < 0.25 ? 5 : -5; // from 0 to 180 right pointer, 180 to 360 left pointer
            var middleLine = (0, circle_1.getCoordinates)(avPercent, radius, radius);
            currentPercent += percent + err;
            var _e = (0, circle_1.getCoordinates)(currentPercent, radius, radius, _this._points.length > 1 && stroke ? -defaultStrokeWidth / 2 : null), endX = _e[0], endY = _e[1];
            var largeArcFlag = percent > 0.5 ? 1 : 0;
            var _f = [5, 30], startPart = _f[0], endPart = _f[1];
            var _g = [
                (0, circle_1.getCoordinates)(avPercent, radius + startPart, radius + startPart),
                (0, circle_1.getCoordinates)(avPercent, radius + endPart, radius + endPart),
            ], linkStart = _g[0], linkEnd = _g[1];
            var isRight = avPercent > -0.25 && avPercent < 0.25;
            var isUp = avPercent > 0.5 || avPercent < 0;
            var className = avPercent > -0.25 && avPercent < 0.25 ? "pie-value start-text" : "pie-value end-text";
            if ((showText || showTextTemplate) && showText !== false) {
                var linkText = (0, dom_1.sv)("text", {
                    x: middleLine[0] * 0.7,
                    y: middleLine[1] * 0.7,
                    class: "pie-inner-value",
                    "aria-hidden": "true",
                }, [
                    showTextTemplate
                        ? (0, common_1.verticalCenteredText)(showTextTemplate(value.toString()))
                        : (0, common_1.verticalCenteredText)(value.toString()),
                ]);
                link.push(linkText);
            }
            var totalValue = typeof valueTemplate === "function"
                ? valueTemplate(percent)
                : Math.round(percent * 100) + "%";
            switch (_this.config.subType) {
                case "basic": {
                    var textPadding = 10;
                    var dy = isUp ? -textPadding * 2 : textPadding;
                    var linkStart_1 = (0, circle_1.getCoordinates)(avPercent, radius + 10, radius + 10);
                    var className2 = isRight ? "donut-value start-text" : "donut-value end-text";
                    var currentText_1 = {
                        text1: {
                            x: useLines ? linkEnd[0] : linkStart_1[0],
                            y: (useLines ? linkEnd[1] : linkStart_1[1]) + dy,
                            width: 0,
                            height: 0,
                            class: "",
                        },
                        text2: {
                            x: useLines ? linkEnd[0] : linkStart_1[0],
                            y: (useLines ? linkEnd[1] : linkStart_1[1]) + dy + 16,
                            width: 0,
                            height: 0,
                            class: "",
                        },
                        changeSector: false,
                        line: lineLength,
                        right: isRight,
                        dy: dy,
                    };
                    var text1 = (0, dom_1.sv)("text", __assign({ x: useLines ? linkEnd[0] : linkStart_1[0], y: (useLines ? linkEnd[1] : linkStart_1[1]) + dy, class: className }, getPointAriaAttrs(value, text, percent)), [(0, common_1.verticalCenteredText)(text.toString())]);
                    var text2 = (0, dom_1.sv)("text", {
                        x: useLines ? linkEnd[0] : linkStart_1[0],
                        y: (useLines ? linkEnd[1] : linkStart_1[1]) + dy + 16,
                        class: className2,
                        "aria-hidden": "true",
                    }, [(0, common_1.verticalCenteredText)(value.toString())]);
                    _a = (0, common_1.getSizesSVGText)(text.toString(), {
                        font: "normal 14px Roboto",
                        lineHeight: 14,
                    }), currentText_1.text1.width = _a[0], currentText_1.text1.height = _a[1];
                    _b = (0, common_1.getSizesSVGText)(value.toString(), {
                        font: "normal 12px Roboto",
                        lineHeight: 12,
                    }), currentText_1.text2.width = _b[0], currentText_1.text2.height = _b[1];
                    var textRadius_1 = useLines ? radius + endPart : radius + 10;
                    if (textPoints.length !== 0) {
                        if (isRight) {
                            textPoints.forEach(function (previosText) {
                                (0, common_1.checkPositions)(currentText_1.text1, previosText.text1, textRadius_1, textRadius_1, currentText_1);
                                (0, common_1.checkPositions)(currentText_1.text1, previosText.text2, textRadius_1, textRadius_1, currentText_1);
                            });
                            if (currentText_1.text1.class)
                                currentText_1.text2.class = currentText_1.text1.class;
                        }
                        else {
                            textPoints.forEach(function (previosText) {
                                (0, common_1.checkPositions)(currentText_1.text2, previosText.text2, textRadius_1, textRadius_1, currentText_1);
                                (0, common_1.checkPositions)(currentText_1.text2, previosText.text1, textRadius_1, textRadius_1, currentText_1);
                            });
                            if (currentText_1.text2.class)
                                currentText_1.text1.class = currentText_1.text2.class;
                        }
                        text1.attrs.x = currentText_1.text1.x;
                        text1.attrs.y = currentText_1.text1.y;
                        text2.attrs.x = currentText_1.text2.x;
                        text2.attrs.y = currentText_1.text2.y;
                        if (currentText_1.text1.class || currentText_1.text2.class) {
                            text1.attrs.class = currentText_1.text1.class;
                            text2.attrs.class = currentText_1.text2.class;
                        }
                        lineLength = currentText_1.line;
                        if (useLines) {
                            linkEnd[0] = currentText_1.text1.x;
                            linkEnd[1] = currentText_1.text1.y - dy;
                        }
                        else {
                            linkStart_1[0] = currentText_1.text1.x;
                            linkStart_1[1] = currentText_1.text1.y - dy;
                        }
                    }
                    textPoints.push(currentText_1);
                    var text3 = (0, dom_1.sv)("text", {
                        x: middleLine[0] * 0.5,
                        y: middleLine[1] * 0.5,
                        class: "pie-inner-value",
                        "aria-hidden": "true",
                    }, [(0, common_1.verticalCenteredText)(totalValue)]);
                    link.push((0, dom_1.sv)("g", {
                        id: "".concat(id, "-text"),
                        class: "chart donut",
                    }, [text1, text2]));
                    link.push(text3);
                    break;
                }
                case "valueOnly": {
                    var valueText = (0, dom_1.sv)("text", {
                        x: middleLine[0] * 0.5,
                        y: middleLine[1] * 0.5,
                        class: "pie-inner-value",
                        "aria-hidden": "true",
                    }, [(0, common_1.verticalCenteredText)("".concat(value))]);
                    link.push(valueText);
                    break;
                }
                case "percentOnly": {
                    var percentText = (0, dom_1.sv)("text", {
                        x: middleLine[0] * 0.5,
                        y: middleLine[1] * 0.5,
                        class: "pie-inner-value",
                        "aria-hidden": "true",
                    }, [(0, common_1.verticalCenteredText)(totalValue)]);
                    link.push(percentText);
                    break;
                }
            }
            if (useLines && subType === "basic") {
                var dy = isUp ? 3 : 0;
                link.push((0, dom_1.sv)("path", {
                    d: "M".concat(linkStart[0], " ").concat(linkStart[1], " L").concat(linkEnd[0], "\n\t\t\t\t\t\t\t").concat(linkEnd[1] + dy, " h ").concat(lineLength),
                    id: "".concat(id, "-connector"),
                    class: "pie-value-connector chart pie",
                }));
            }
            var centerX = 0, centerY = 0;
            var pseudoRadius = defaultStrokeWidth / (2 * Math.sin(Math.PI / _this._points.length));
            if (_this._points.length > 1 && stroke) {
                _c = (0, circle_1.getCoordinates)(avPercent, pseudoRadius, pseudoRadius), centerX = _c[0], centerY = _c[1];
            }
            var d = "M ".concat(startX, " ").concat(startY, " A ").concat(radius, " ").concat(radius, " 0 ").concat(largeArcFlag, " 1 ").concat(endX, " ").concat(endY, "\n\t\t\t\tL ").concat(0 + centerX, " ").concat(0 + centerY);
            var _h = (0, circle_1.getCoordinates)(avPercent, 4, 4), shiftX = _h[0], shiftY = _h[1];
            var currentSector = (0, dom_1.sv)("path", {
                d: d,
                class: "chart pie",
                _key: id,
                fill: fill,
                onclick: [_this._handlers.onclick, item[1], item[2]],
                // eslint-disable-next-line @typescript-eslint/unbound-method
                onmouseover: [circle_1.pieLikeHandlers.onmouseover, shiftX, shiftY],
                // eslint-disable-next-line @typescript-eslint/unbound-method
                onmouseout: [circle_1.pieLikeHandlers.onmouseout],
                role: "presentation",
            });
            link.unshift(currentSector);
            pie.push((0, dom_1.sv)("g", { id: id }, link));
            if (_this._points.length === 1) {
                tooltipData.push([width / 2, height / 2]);
            }
            else {
                tooltipData.push([middleLine[0] * 0.7 + width / 2, middleLine[1] * 0.7 + height / 2]);
            }
        });
        this._center = [width / 2, height / 2];
        this._tooltipData = tooltipData;
        svg.push((0, dom_1.sv)("defs", defs));
        svg = svg.concat(pie);
        svg = svg.concat(lines);
        return (0, dom_1.sv)("g", __assign(__assign({ transform: "translate(".concat(width / 2, ", ").concat(height / 2, ")") }, getChartAriaAttrs(this.config)), { tabindex: 0 }), svg);
    };
    return Pie;
}(NoScaleSeria_1.default));
exports.default = Pie;


/***/ }),
/* 71 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
var dom_1 = __webpack_require__(1);
var circle_1 = __webpack_require__(10);
var common_1 = __webpack_require__(2);
var NoScaleSeria_1 = __webpack_require__(18);
var Pie = /** @class */ (function (_super) {
    __extends(Pie, _super);
    function Pie() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    // todo move to NoScaleSeria
    Pie.prototype.paint = function (width, height) {
        var _this = this;
        var getPointAriaAttrs = function (value, text, percent) { return ({
            role: "graphics-symbol",
            "aria-roledescription": "piece of pie",
            "aria-label": "".concat(text || "", ", ").concat(value, " (").concat((0, common_1.roundToTwoNumAfterPoint)(percent), "%)"),
        }); };
        var getChartAriaAttrs = function (cfg) { return ({
            "aria-label": "chart ".concat(cfg.text || ""),
        }); };
        var _a = this.config, subType = _a.subType, useLines = _a.useLines, showText = _a.showText, showTextTemplate = _a.showTextTemplate, valueTemplate = _a.valueTemplate;
        var radiusX;
        if (height > width) {
            radiusX = width / 2;
        }
        else {
            radiusX = height / 2;
        }
        var radiusY = radiusX * 0.5;
        var connector = radiusX / 5;
        var tooltipData = [];
        var currentPercent = -0.25;
        var svg = [];
        var links = [];
        var textPoints = [];
        this._points.forEach(function (item, index) {
            var _a, _b;
            var percent = item[0], value = item[1], id = item[2], text = item[3], color = item[4];
            var err = percent === 0 || percent === 1 ? -0.000001 : 0;
            var _c = (0, circle_1.getCoordinates)(currentPercent, radiusX, radiusY), startX = _c[0], startY = _c[1];
            var avPercent = currentPercent + percent / 2;
            var lineLength = avPercent < 0.25 ? 5 : -5;
            var middleLine = (0, circle_1.getCoordinates)(avPercent, radiusX, radiusY);
            var delta = 0;
            if (avPercent > 0 && avPercent < 0.5) {
                delta = connector * Math.sin(2 * Math.PI * avPercent);
            }
            var linkStart = (0, circle_1.getCoordinates)(avPercent, radiusX + 5 + delta, radiusY + 5 + delta);
            var linkEnd = (0, circle_1.getCoordinates)(avPercent, radiusX + 30 + delta, radiusY + 30 + delta);
            var nextPercent = currentPercent + percent + err;
            var _d = (0, circle_1.getCoordinates)(nextPercent, radiusX, radiusY), endX = _d[0], endY = _d[1];
            var largeArcFlag = percent > 0.5 ? 1 : 0;
            var isRight = avPercent > -0.25 && avPercent < 0.25;
            var isUp = avPercent > 0.5 || avPercent < 0;
            var className = isRight ? "pie-value start-text" : "pie-value end-text";
            var totalValue = typeof valueTemplate === "function"
                ? valueTemplate(percent)
                : Math.round(percent * 100) + "%";
            switch (_this.config.subType) {
                case "basic": {
                    var textPadding = 10;
                    var dy = isUp ? -textPadding * 2 : textPadding;
                    var className2 = isRight ? "donut-value start-text" : "donut-value end-text";
                    var currentText_1 = {
                        text1: {
                            x: useLines ? linkEnd[0] : linkStart[0],
                            y: (useLines ? linkEnd[1] : linkStart[1]) + dy,
                            width: 0,
                            height: 0,
                            class: "",
                        },
                        text2: {
                            x: useLines ? linkEnd[0] : linkStart[0],
                            y: (useLines ? linkEnd[1] : linkStart[1]) + dy + 16,
                            width: 0,
                            height: 0,
                            class: "",
                        },
                        changeSector: false,
                        line: lineLength,
                        right: isRight,
                        dy: dy,
                    };
                    var text1 = (0, dom_1.sv)("text", __assign({ x: useLines ? linkEnd[0] : linkStart[0], y: (useLines ? linkEnd[1] : linkStart[1]) + dy, dx: useLines ? (lineLength / 2 + lineLength > 0 ? 10 : -10) : null, class: className }, getPointAriaAttrs(value, text, percent)), [(0, common_1.verticalCenteredText)(text.toString())]);
                    var text2 = (0, dom_1.sv)("text", {
                        x: useLines ? linkEnd[0] : linkStart[0],
                        y: (useLines ? linkEnd[1] : linkStart[1]) + dy + 16,
                        dx: useLines ? (lineLength / 2 + lineLength > 0 ? 10 : -10) : null,
                        class: className2,
                        "aria-hidden": "true",
                    }, [(0, common_1.verticalCenteredText)(value.toString())]);
                    var text3 = (0, dom_1.sv)("text", {
                        x: middleLine[0] * 0.5,
                        y: middleLine[1] * 0.5,
                        class: "pie-inner-value",
                        "aria-hidden": "true",
                    }, [(0, common_1.verticalCenteredText)(totalValue)]);
                    _a = (0, common_1.getSizesSVGText)(text.toString(), {
                        font: "normal 14px Roboto",
                        lineHeight: 14,
                    }), currentText_1.text1.width = _a[0], currentText_1.text1.height = _a[1];
                    _b = (0, common_1.getSizesSVGText)(value.toString(), {
                        font: "normal 12px Roboto",
                        lineHeight: 12,
                    }), currentText_1.text2.width = _b[0], currentText_1.text2.height = _b[1];
                    var textRadiusX_1 = useLines ? radiusX + 30 + delta : radiusX + 5 + delta;
                    var textRadiusY_1 = useLines ? radiusY + 30 + delta : radiusY + 5 + delta;
                    if (textPoints.length !== 0) {
                        // const previosText = textPoints[index - 1];
                        if (isRight) {
                            textPoints.forEach(function (previosText) {
                                (0, common_1.checkPositions)(currentText_1.text1, previosText.text1, textRadiusX_1, textRadiusY_1, currentText_1);
                                (0, common_1.checkPositions)(currentText_1.text1, previosText.text2, textRadiusX_1, textRadiusY_1, currentText_1);
                            });
                            if (currentText_1.text1.class)
                                currentText_1.text2.class = currentText_1.text1.class;
                        }
                        else {
                            textPoints.forEach(function (previosText) {
                                (0, common_1.checkPositions)(currentText_1.text2, previosText.text2, textRadiusX_1, textRadiusY_1, currentText_1);
                                (0, common_1.checkPositions)(currentText_1.text2, previosText.text1, textRadiusX_1, textRadiusY_1, currentText_1);
                            });
                            if (currentText_1.text2.class)
                                currentText_1.text1.class = currentText_1.text2.class;
                        }
                        text1.attrs.x = currentText_1.text1.x;
                        text1.attrs.y = currentText_1.text1.y;
                        text2.attrs.x = currentText_1.text2.x;
                        text2.attrs.y = currentText_1.text2.y;
                        if (currentText_1.text1.class || currentText_1.text2.class) {
                            text1.attrs.class = currentText_1.text1.class;
                            text2.attrs.class = currentText_1.text2.class;
                        }
                        lineLength = currentText_1.line;
                        if (useLines) {
                            linkEnd[0] = currentText_1.text1.x;
                            linkEnd[1] = currentText_1.text1.y - dy;
                        }
                        else {
                            linkStart[0] = currentText_1.text1.x;
                            linkStart[1] = currentText_1.text1.y - dy;
                        }
                    }
                    textPoints.push(currentText_1);
                    links.push(text1, text2, text3);
                    break;
                }
                case "valueOnly": {
                    var valueText = (0, dom_1.sv)("text", {
                        x: middleLine[0] * 0.5,
                        y: middleLine[1] * 0.5,
                        class: "pie-inner-value",
                        "aria-hidden": "true",
                    }, [(0, common_1.verticalCenteredText)(value.toString())]);
                    links.push(valueText);
                    break;
                }
                case "percentOnly": {
                    var percentText = (0, dom_1.sv)("text", {
                        x: middleLine[0] * 0.5,
                        y: middleLine[1] * 0.5,
                        class: "pie-inner-value",
                        "aria-hidden": "true",
                    }, [(0, common_1.verticalCenteredText)(totalValue)]);
                    links.push(percentText);
                    break;
                }
            }
            if (useLines && subType === "basic") {
                links.push((0, dom_1.sv)("path", {
                    d: "M".concat(linkStart[0], " ").concat(linkStart[1], " L").concat(linkEnd[0], " ").concat(linkEnd[1], " h ").concat(lineLength),
                    class: "pie-value-connector",
                }));
            }
            if ((showText || showTextTemplate) && showText !== false) {
                var textSvg = (0, dom_1.sv)("text", {
                    x: middleLine[0] * 0.7,
                    y: middleLine[1] * 0.7,
                    class: "pie-inner-value",
                    "aria-hidden": "true",
                }, [
                    showTextTemplate
                        ? (0, common_1.verticalCenteredText)(showTextTemplate(value))
                        : (0, common_1.verticalCenteredText)(value.toString()),
                ]);
                links.push(textSvg);
            }
            // 3d block
            var addition = "";
            if (currentPercent <= 0 && nextPercent >= 0.5) {
                addition = "M ".concat(radiusX, " 0 v ").concat(connector, " A ").concat(radiusX, " ").concat(radiusY, " 0 1 1 ").concat(-radiusX, " ").concat(connector, " v ").concat(-connector);
            }
            else if (currentPercent <= 0 && nextPercent < 0.5) {
                addition = "M ".concat(radiusX, " 0 v ").concat(connector, " A ").concat(radiusX, " ").concat(radiusY, " 0 0 1 ").concat(endX, " ").concat(endY +
                    connector, " v ").concat(-connector);
            }
            else if (currentPercent > 0 && currentPercent <= 0.5 && nextPercent >= 0.5) {
                addition = "M ".concat(startX, " ").concat(startY, " v ").concat(connector, " A ").concat(radiusX, " ").concat(radiusY, " 0 0 1 ").concat(-radiusX, " ").concat(connector, " v ").concat(-connector);
            }
            else if (currentPercent > 0 && nextPercent < 0.5) {
                addition = "M ".concat(startX, " ").concat(startY, " v ").concat(connector, " A ").concat(radiusX, " ").concat(radiusY, " 0 0 1 ").concat(endX, " ").concat(endY +
                    connector, " v ").concat(-connector);
            }
            if (addition) {
                var additionPath = (0, dom_1.sv)("path", {
                    _key: id + "__shadow__",
                    d: addition,
                    fill: color || (0, common_1.getDefaultColor)(index),
                    onclick: [_this._handlers.onclick, item[1], item[2]],
                    class: "chart pie3d addition",
                    stroke: "none",
                    filter: "url(#shadow)",
                    role: "presentation",
                });
                svg.push(additionPath);
            }
            // end 3d block
            var d = "M ".concat(startX, " ").concat(startY, " A ").concat(radiusX, " ").concat(radiusY, " 0 ").concat(largeArcFlag, " 1 ").concat(endX, " ").concat(endY, " L 0 0");
            svg.push((0, dom_1.sv)("path", {
                d: d,
                _key: id,
                fill: color || (0, common_1.getDefaultColor)(index),
                stroke: "none",
                onclick: [_this._handlers.onclick, item[1], item[2]],
                class: "chart pie3d",
                role: "presentation",
            }));
            if (_this._points.length === 1) {
                tooltipData.push([width / 2, height / 2]);
            }
            else {
                tooltipData.push([middleLine[0] * 0.7 + width / 2, middleLine[1] * 0.7 + height / 2]);
            }
            currentPercent = nextPercent;
        });
        this._center = [width / 2, height / 2];
        this._tooltipData = tooltipData;
        svg = svg.concat(links);
        return (0, dom_1.sv)("g", __assign(__assign({ transform: "translate(".concat(width / 2, ", ").concat(height / 2, ")") }, getChartAriaAttrs(this.config)), { tabindex: 0 }), svg);
    };
    return Pie;
}(NoScaleSeria_1.default));
exports.default = Pie;


/***/ }),
/* 72 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
var dom_1 = __webpack_require__(1);
var circle_1 = __webpack_require__(10);
var common_1 = __webpack_require__(2);
var BaseSeria_1 = __webpack_require__(11);
var Radar = /** @class */ (function (_super) {
    __extends(Radar, _super);
    function Radar() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    Radar.prototype.addScale = function (type, scale) {
        this._scale = scale;
    };
    Radar.prototype.scaleReady = function (sizes) {
        for (var key in sizes) {
            sizes[key] += this.config.paddings;
        }
        return sizes;
    };
    Radar.prototype.dataReady = function (prev) {
        var _this = this;
        if (!this.config.active) {
            return (this._points = []);
        }
        var xLocator = (0, common_1.locator)(this._scale.config.value);
        this._points = this._data.map(function (item, index) {
            // raw values
            var value = _this._locator(item);
            var set = [value, value, item.id, value, value];
            if (prev) {
                set[1] += prev[index][1];
            }
            if (xLocator) {
                set.push(xLocator(item));
            }
            return set;
        });
        return this._points;
    };
    Radar.prototype.getTooltipText = function (id) {
        if (this.config.tooltip) {
            var p = this._defaultLocator(this._data.getItem(id));
            if (this.config.tooltipTemplate) {
                return this.config.tooltipTemplate(p);
            }
            return p;
        }
    };
    Radar.prototype.paint = function (width, height) {
        var _this = this;
        _super.prototype.paint.call(this, width, height);
        var getPointAriaAttrs = function (key, points) {
            var point;
            if (key) {
                point = points.find(function (p) { return key.includes(p[2]); });
            }
            return {
                role: "graphics-symbol",
                "aria-roledescription": "point",
                "aria-label": point ? "point x=".concat(point[5], " y=").concat(point[4]) : "",
                tabindex: 0,
            };
        };
        var getChartAriaAttrs = function (cfg) { return ({
            "aria-label": "chart ".concat(cfg.value || ""),
        }); };
        if (!this.config.active) {
            return;
        }
        var config = this.config;
        var svg = [];
        var d = this._points.map(function (item, first) { return (first ? "L" : "M") + "".concat(item[0], " ").concat(item[1]); }).join(" ") + "Z";
        svg.push((0, dom_1.sv)("path", {
            d: d,
            stroke: config.color,
            "stroke-width": config.strokeWidth,
            fill: config.fill,
            "fill-opacity": config.alpha,
            class: "chart radar",
        }));
        if (config.pointType) {
            var points = this._points
                .map(function (p) { return _this._drawPointType(p[0], p[1], (0, common_1.calcPointRef)(p[2], _this.id)); })
                .map(function (node) {
                if (node && node.attrs) {
                    node.attrs = __assign(__assign({}, node.attrs), getPointAriaAttrs(node.key, _this._points));
                }
                return node;
            });
            svg.push((0, dom_1.sv)("g", points));
        }
        return (0, dom_1.sv)("g", __assign(__assign({ id: "seria" + config.id }, getChartAriaAttrs(this.config)), { tabindex: 0 }), svg);
    };
    Radar.prototype._calckFinalPoints = function (width, height) {
        var _this = this;
        var radius;
        if (height > width) {
            radius = width / 2;
        }
        else {
            radius = height / 2;
        }
        var scalePercent = 1 / this._data.getLength();
        var currentPercent = -0.25 - scalePercent;
        this._points.forEach(function (item, index) {
            currentPercent += scalePercent;
            var value = _this._scale.point(item[0]);
            var real = (0, circle_1.getCoordinates)(currentPercent, value * radius, value * radius);
            // scaled values
            item[0] = real[0] + width / 2;
            item[1] = real[1] + height / 2;
        });
    };
    Radar.prototype._defaultLocator = function (v) {
        return this._locator(v);
    };
    Radar.prototype._setDefaults = function (config) {
        var defaults = {
            strokeWidth: 2,
            active: true,
            tooltip: true,
            paddings: 5,
            color: "none",
            fill: "none",
            pointType: "circle",
        };
        this._locator = (0, common_1.locator)(config.value);
        config.scales = config.scales || ["radial"];
        this.config = __assign(__assign({}, defaults), config);
        if (this.config.pointType) {
            var color = this.config.pointColor || this.config.color;
            this._drawPointType = this._getPointType(this.config.pointType, color);
        }
    };
    return Radar;
}(BaseSeria_1.default));
exports.default = Radar;


/***/ }),
/* 73 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
var Line_1 = __webpack_require__(19);
var common_1 = __webpack_require__(2);
var Scatter = /** @class */ (function (_super) {
    __extends(Scatter, _super);
    function Scatter() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    Scatter.prototype.addScale = function (type, scale) {
        if (type === "bottom" || type === "top") {
            this.xScale = scale;
            this._xLocator = (0, common_1.locator)(this.config.value);
        }
        else {
            this.yScale = scale;
            this._yLocator = (0, common_1.locator)(this.config.valueY);
        }
    };
    Scatter.prototype._setDefaults = function (config) {
        var defaults = {
            active: true,
            tooltip: true,
            pointType: "rect",
        };
        this.config = __assign(__assign({}, defaults), config);
        var point = this.config.pointType;
        var color = this.config.pointColor || this.config.color;
        if (point) {
            this._drawPointType = this._getPointType(point, color);
        }
    };
    return Scatter;
}(Line_1.default));
exports.default = Scatter;


/***/ }),
/* 74 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
Object.defineProperty(exports, "__esModule", { value: true });
var dom_1 = __webpack_require__(1);
var spline_1 = __webpack_require__(31);
var Line_1 = __webpack_require__(19);
var Spline = /** @class */ (function (_super) {
    __extends(Spline, _super);
    function Spline() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    Spline.prototype._getForm = function (points, config, width, height) {
        var color = config.color, strokeWidth = config.strokeWidth;
        var d = (0, spline_1.default)(points);
        return (0, dom_1.sv)("path", {
            id: "seria" + config.id,
            d: d,
            class: this._getCss(),
            stroke: color,
            "stroke-width": strokeWidth,
            fill: "none",
        });
    };
    return Spline;
}(Line_1.default));
exports.default = Spline;


/***/ }),
/* 75 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
Object.defineProperty(exports, "__esModule", { value: true });
var dom_1 = __webpack_require__(1);
var spline_1 = __webpack_require__(31);
var Area_1 = __webpack_require__(27);
var SplineArea = /** @class */ (function (_super) {
    __extends(SplineArea, _super);
    function SplineArea() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    SplineArea.prototype._form = function (width, height, svg, prev) {
        var _a = this.config, fill = _a.fill, alpha = _a.alpha, strokeWidth = _a.strokeWidth, color = _a.color, id = _a.id;
        var d = "";
        var first = this._points[0];
        if (prev) {
            d = "".concat((0, spline_1.default)([].concat(prev).reverse()), " ").concat((0, spline_1.default)(this._points, true), " Z");
        }
        else {
            d = "M".concat(first[0], " ").concat(height, " V ").concat(first[1], " ").concat((0, spline_1.default)(this._points), " V").concat(height, " H ").concat(first[0]);
        }
        if (strokeWidth) {
            var line = (0, spline_1.default)(this._points);
            var splinePath = (0, dom_1.sv)("path", {
                d: line,
                "stroke-width": strokeWidth,
                stroke: color,
                fill: "none",
                "stroke-linecap": "butt",
                class: this._getCss(),
            });
            svg.push(splinePath);
        }
        var path = (0, dom_1.sv)("path", {
            id: "seria" + id,
            d: d,
            class: this._getCss(),
            fill: fill,
            "fill-opacity": alpha,
            stroke: "none",
        });
        svg.push(path);
        return svg;
    };
    return SplineArea;
}(Area_1.default));
exports.default = SplineArea;


/***/ }),
/* 76 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
var core_1 = __webpack_require__(0);
var dom_1 = __webpack_require__(1);
var ts_data_1 = __webpack_require__(7);
var common_1 = __webpack_require__(2);
var types_1 = __webpack_require__(9);
var BaseSeria_1 = __webpack_require__(11);
var TreeMap = /** @class */ (function (_super) {
    __extends(TreeMap, _super);
    function TreeMap() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    TreeMap.prototype.scaleReady = function (sizes) {
        for (var key in sizes) {
            sizes[key] += this.config.paddings;
        }
        return sizes;
    };
    TreeMap.prototype.toggle = function (id) {
        var serie = this.config.treeSeries.find(function (item) { return item.id === id; });
        if (serie)
            serie.active = !serie.active;
    };
    TreeMap.prototype.dataReady = function () {
        var _this = this;
        if (!this.config.active) {
            return (this._points = []);
        }
        var isGruopName = this.config.legendType === "groupName";
        this._sum = 0;
        this._data.forEach(function (item) {
            var parent = _this._data.getItem(_this._data.getParent(item.id));
            if (parent && parent.$hidden && _this._maxLevel > 1)
                return;
            var value = _this._valueLocator(item);
            if (value) {
                var serie = _this._getSerie(isGruopName ? item.id : value, isGruopName);
                item.color = serie ? serie.color : item.color;
                item.$hidden = serie ? !serie.active : false;
                if (!item.$hidden && value)
                    _this._sum += parseFloat(item[_this.config.value]);
            }
        });
        this._points = [];
        this._data.eachChild(this._data.getRoot(), function (item) {
            var _a;
            if (item.$hidden)
                return;
            var value = _this._valueLocator(item);
            var text = _this._textLocator(item);
            var children = [];
            if (!value && _this._data.haveItems(item.id)) {
                value = 0;
                _this._data.eachChild(item.id, function (child) {
                    var _a;
                    if (child.$hidden)
                        return;
                    var childValue = _this._valueLocator(child);
                    var childText = _this._textLocator(child);
                    var childColor = child.color ||
                        ((_a = _this._getSerie(isGruopName ? child.id : childValue, isGruopName)) === null || _a === void 0 ? void 0 : _a.color);
                    if (childValue)
                        value += childValue;
                    children.push([0, childValue, child.id, childText, childColor]);
                });
            }
            item.percent = parseFloat(value) / _this._sum;
            var color = item.color || ((_a = _this._getSerie(isGruopName ? item.id : value, isGruopName)) === null || _a === void 0 ? void 0 : _a.color);
            _this._points.push([
                item.percent,
                value,
                item.id.toString(),
                text,
                color,
                { items: children },
            ]);
        }, false);
        this._maxLevel = this._getMaxLevel();
        return this._points;
    };
    TreeMap.prototype.paint = function (width, height) {
        var _this = this;
        var hasActiveSerie = !!this.config.treeSeries.find(function (item) { return item.active; });
        if (!hasActiveSerie)
            return;
        var getChartAriaAttrs = function (cfg) { return ({
            "aria-label": "chart ".concat(cfg.text || ""),
        }); };
        var strokeWidth = this.config.strokeWidth;
        var defs = [];
        var bar = [];
        var svg = [];
        var outerStrokeWidth = this._maxLevel > 1 ? strokeWidth * 2 : strokeWidth;
        var restWidth = width - outerStrokeWidth;
        var restHeight = height - outerStrokeWidth;
        this._layout = {
            id: (0, core_1.uid)(),
            area: restWidth * restHeight,
            width: restWidth,
            height: restHeight,
            restArea: restWidth * restHeight,
            restWidth: restWidth,
            restHeight: restHeight,
            startX: outerStrokeWidth / 2,
            startY: outerStrokeWidth / 2,
            stroke: outerStrokeWidth,
        };
        var currentX = this._layout.startX;
        var currentY = this._layout.startY;
        if (this._layout.width >= this._layout.height) {
            this._layout["cols"] = [];
        }
        else {
            this._layout["rows"] = [];
        }
        this._points.sort(function (a, b) { return b[0] - a[0]; });
        this._points.forEach(function (item, index, array) {
            var _a, _b;
            // eslint-disable-next-line @typescript-eslint/no-unused-vars
            var percent = item[0], value = item[1], id = item[2], text = item[3], color = item[4], children = item[5];
            if (percent === 0)
                return;
            var _c = _this._drawBar(item, index, array, _this._layout, currentX, currentY, !!(children && ((_a = children.items) === null || _a === void 0 ? void 0 : _a.length))), currentSector = _c[0], linkText = _c[1], clip = _c[2], nextX = _c[3], nextY = _c[4], currentSectorObj = _c[5], groupNameSector = _c[6];
            currentX = currentSectorObj[0];
            currentY = currentSectorObj[1];
            bar.push((0, dom_1.sv)("g", { id: id }, [currentSector, linkText, groupNameSector]));
            defs.push(clip);
            if (children && ((_b = children.items) === null || _b === void 0 ? void 0 : _b.length)) {
                var childWidth = currentSectorObj[2] - strokeWidth;
                var childHeight = currentSectorObj[3] - strokeWidth;
                var childLayout_1 = {
                    id: (0, core_1.uid)(),
                    parent: _this._layout.id,
                    area: childWidth * childHeight,
                    width: childWidth,
                    height: childHeight,
                    restArea: childWidth * childHeight,
                    restWidth: childWidth,
                    restHeight: childHeight,
                    startX: currentX + strokeWidth / 2,
                    startY: currentY +
                        strokeWidth / 2 +
                        (_this.config.direction === "asc" || !groupNameSector ? 0 : strokeWidth / 2),
                    stroke: strokeWidth,
                };
                var childCurrentX_1 = childLayout_1.startX;
                var childCurrentY_1 = childLayout_1.startY;
                if (childLayout_1.width >= childLayout_1.height) {
                    childLayout_1["cols"] = [];
                }
                else
                    childLayout_1["rows"] = [];
                children.items.map(function (child) { return (child[0] = child[1] / item[1]); });
                children.items.sort(function (a, b) { return b[0] - a[0]; });
                children.items.forEach(function (child, childInd, childArr) {
                    var childId = child[2];
                    var _a = _this._drawBar(child, childInd, childArr, childLayout_1, childCurrentX_1, childCurrentY_1, false), childSector = _a[0], childLinkText = _a[1], childClip = _a[2], childNextX = _a[3], childNextY = _a[4];
                    bar.push((0, dom_1.sv)("g", { id: childId }, [childSector, childLinkText]));
                    defs.push(childClip);
                    childCurrentX_1 = childNextX;
                    childCurrentY_1 = childNextY;
                });
            }
            currentX = nextX;
            currentY = nextY;
        });
        svg.push((0, dom_1.sv)("defs", defs));
        svg = svg.concat(bar);
        return (0, dom_1.sv)("g", __assign(__assign({ id: "seria" + this.config.id }, getChartAriaAttrs(this.config)), { tabindex: 0, transform: this.config.direction === "asc"
                ? "rotate(180 ".concat(this._layout.startX + this._layout.width / 2, " ").concat(this._layout.startY +
                    this._layout.height / 2, ")")
                : "" }), svg);
    };
    TreeMap.prototype._drawBar = function (item, index, array, layout, currentX, currentY, isParent) {
        var _this = this;
        var getPointAriaAttrs = function (value, text, percent) { return ({
            role: "graphics-symbol",
            "aria-roledescription": "piece of treeMap",
            "aria-label": "".concat(text || "", ", ").concat(value, " (").concat((0, common_1.roundToTwoNumAfterPoint)(percent), "%)"),
        }); };
        var percent = item[0], value = item[1], id = item[2], text = item[3], color = item[4];
        var _a = this.config, stroke = _a.stroke, showText = _a.showText, showTextTemplate = _a.showTextTemplate;
        var itemArea = percent * layout.area;
        var parentObj = this._getDeepParent(layout);
        var isGroup = isParent && percent > 0 && text;
        var maxX = layout.startX + layout.width - parentObj.restWidth;
        var maxY = layout.startY + layout.height - parentObj.restHeight;
        currentX = currentX > maxX ? maxX : currentX;
        currentY = currentY > maxY ? maxY : currentY;
        var _b = this._getBar(parentObj, itemArea, currentX, currentY, array, index, layout.area), currentSectorObj = _b[0], linkTextObj = _b[1], nextX = _b[2], nextY = _b[3], parentId = _b[4];
        if (currentSectorObj[2] < 0 || currentSectorObj[3] < 0)
            return;
        this._recountParentArea(itemArea, parentId, layout);
        var groupRect = isGroup
            ? (0, dom_1.sv)("rect", {
                x: currentSectorObj[0] + layout.stroke / 4,
                y: this.config.direction === "asc"
                    ? currentSectorObj[1] +
                        currentSectorObj[3] -
                        this._headerHeight -
                        layout.stroke / 2
                    : currentSectorObj[1] + layout.stroke / 4,
                width: currentSectorObj[2] - layout.stroke / 2,
                height: this._headerHeight + layout.stroke / 4,
                stroke: stroke,
                "stroke-width": layout.stroke / 2,
                class: "treeMap-header",
                _key: id,
                onclick: function () { return _this._toggleGroup(id); },
            })
            : null;
        var groupName = isGroup
            ? (0, dom_1.sv)("text", {
                x: currentSectorObj[0] + currentSectorObj[2] / 2,
                y: this.config.direction === "asc"
                    ? currentSectorObj[1] +
                        currentSectorObj[3] -
                        this._headerHeight / 2 -
                        layout.stroke / 2
                    : currentSectorObj[1] + this._headerHeight / 2 + layout.stroke / 2,
                class: "header-text",
                "aria-hidden": "true",
                transform: this.config.direction === "asc"
                    ? "rotate(180 ".concat(currentSectorObj[0] +
                        currentSectorObj[2] / 2, " ").concat(currentSectorObj[1] +
                        currentSectorObj[3] -
                        this._headerHeight / 2 -
                        layout.stroke / 2, ")")
                    : "",
            }, [
                showTextTemplate
                    ? (0, common_1.verticalCenteredText)(showTextTemplate(text === null || text === void 0 ? void 0 : text.toString()))
                    : (0, common_1.verticalCenteredText)(text === null || text === void 0 ? void 0 : text.toString()),
            ])
            : null;
        var groupNameSector = isParent && percent > 0 && text ? (0, dom_1.sv)("g", {}, [groupRect, groupName]) : null;
        var innerText = showTextTemplate ? showTextTemplate(text === null || text === void 0 ? void 0 : text.toString()) : text === null || text === void 0 ? void 0 : text.toString();
        var sizes = (0, common_1.getSizesSVGText)(innerText, { font: "normal 12px Roboto", lineHeight: 14 });
        var isFit = sizes[0] < currentSectorObj[2] - layout.stroke * 2;
        var clipId = (0, core_1.uid)();
        var clip = currentSectorObj[2] > layout.stroke * 2 && currentSectorObj[3] > layout.stroke * 2
            ? (0, dom_1.sv)("clipPath", {
                id: clipId,
                "clip-rule": "nonzero",
            }, [
                (0, dom_1.sv)("rect", {
                    x: currentSectorObj[0] + layout.stroke,
                    y: currentSectorObj[1] + layout.stroke,
                    width: currentSectorObj[2] - layout.stroke * 2,
                    height: currentSectorObj[3] - layout.stroke * 2,
                    fill: "none",
                    stroke: "black",
                }),
            ])
            : null;
        var currentSector = (0, dom_1.sv)("rect", __assign(__assign({}, getPointAriaAttrs(value, text, percent)), { x: currentSectorObj[0], y: currentSectorObj[1], width: currentSectorObj[2], height: currentSectorObj[3], stroke: stroke, "stroke-width": layout.stroke, class: "chart treeMap", _key: id, fill: color, onclick: [this._handlers.onclick, item[1], item[2]], onmousemove: this._maxLevel > 1 && this._getDataLevel(id) === 1
                ? ""
                : [this._handlers.onmousemove, array[index][2], this.id], onmouseleave: this._maxLevel > 1 && this._getDataLevel(id) === 1
                ? ""
                : [this._handlers.onmouseleave, array[index][2], this.id] }));
        var linkText = showText
            ? (0, dom_1.sv)("text", {
                x: isFit ? linkTextObj[0] : currentSectorObj[0] + layout.stroke,
                y: linkTextObj[1],
                class: "treeMap-inner-value ".concat(isFit ? "" : "start-text"),
                "aria-hidden": "true",
                "clip-path": "url(\"#".concat(clipId, "\")"),
                transform: this.config.direction === "asc"
                    ? "rotate(180 ".concat(linkTextObj[0], " ").concat(linkTextObj[1], ")")
                    : "",
            }, [(0, common_1.verticalCenteredText)(innerText)])
            : null;
        if (isParent && groupNameSector) {
            currentSectorObj[1] += this.config.direction === "asc" ? 0 : this._headerHeight;
            currentSectorObj[3] -= this._headerHeight + layout.stroke / 4;
        }
        return [currentSector, linkText, clip, nextX, nextY, currentSectorObj, groupNameSector];
    };
    TreeMap.prototype._getBar = function (parentObj, itemArea, currentX, currentY, arr, index, startArea) {
        var _a, _b, _c, _d;
        var _this = this;
        var checkNextItem = function (itemObj, startIndex, nextIndex, direction) {
            var sector = {
                area: 0,
                width: itemObj.width,
                height: itemObj.height,
            };
            for (var i = startIndex; i <= nextIndex; i++) {
                sector.area += arr[i][0] * startArea;
            }
            if (direction === "rows") {
                sector.width = sector.area / sector.height;
            }
            else {
                sector.height = sector.area / sector.width;
            }
            var newWidth = direction === "rows" ? sector.width : itemObj.area / sector.height;
            var newHeight = direction === "rows" ? itemObj.area / sector.width : sector.height;
            if ((newWidth / newHeight > _this._aspectRatio || newHeight / newWidth > _this._aspectRatio) &&
                arr[index][0] > 0.01 &&
                nextIndex !== _this._points.length - 1) {
                return checkNextItem(itemObj, startIndex, nextIndex + 1, direction);
            }
            else {
                return [newWidth, newHeight, sector];
            }
        };
        var currentPiece;
        var nextX = currentX;
        var nextY = currentY;
        var direction = parentObj.restWidth > parentObj.restHeight ? "cols" : "rows";
        var crossDirection = direction === "cols" ? "rows" : "cols";
        var itemWidth = direction === "cols" ? itemArea / parentObj.restHeight : parentObj.restWidth;
        var itemHeight = direction === "cols" ? parentObj.restHeight : itemArea / parentObj.restWidth;
        if ((itemHeight / itemWidth > this._aspectRatio || itemWidth / itemHeight > this._aspectRatio) &&
            Math.round(itemArea - parentObj.restArea) !== 0 &&
            index < arr.length - 2 &&
            itemArea < parentObj.restArea) {
            var sector = void 0;
            currentPiece = {
                id: (0, core_1.uid)(),
                area: itemArea,
                width: itemWidth,
                height: itemHeight,
            };
            _a = checkNextItem(currentPiece, index, index + 1, crossDirection), currentPiece.width = _a[0], currentPiece.height = _a[1], sector = _a[2];
            itemWidth = currentPiece.width;
            itemHeight = currentPiece.height;
            sector = (_b = {
                    id: (0, core_1.uid)(),
                    area: sector.area,
                    width: sector.width,
                    height: sector.height,
                    restArea: sector.area,
                    restWidth: crossDirection === "cols" ? sector.width - itemWidth : sector.width,
                    restHeight: crossDirection === "cols" ? sector.height : sector.height - itemHeight
                },
                _b[crossDirection] = [currentPiece],
                _b);
            currentPiece["parent"] = sector.id;
            if (!parentObj[direction] && !parentObj[crossDirection]) {
                parentObj[direction] = [sector];
                sector["parent"] = parentObj.id;
            }
            else if (parentObj[crossDirection]) {
                var parentId = (0, core_1.uid)();
                parentObj[crossDirection].push((_c = {
                        id: parentId,
                        parent: parentObj.id,
                        area: parentObj.restArea,
                        width: parentObj.restWidth,
                        height: parentObj.restHeight,
                        restArea: parentObj.restArea,
                        restWidth: direction === "cols" ? parentObj.restWidth - sector.width : parentObj.restWidth,
                        restHeight: direction === "cols" ? parentObj.restHeight : parentObj.restHeight - sector.height
                    },
                    _c[direction] = [sector],
                    _c));
                sector["parent"] = parentId;
            }
            else {
                parentObj[direction].push(sector);
                sector["parent"] = parentObj.id;
            }
            if (direction === "cols") {
                parentObj.restWidth -= sector.width;
                nextY += itemHeight;
            }
            else {
                parentObj.restHeight -= sector.height;
                nextX += itemWidth;
            }
        }
        else {
            currentPiece = {
                id: (0, core_1.uid)(),
                area: itemArea,
                width: itemWidth,
                height: itemHeight,
            };
            if (parentObj[crossDirection] && parentObj.restArea.toFixed(2) > itemArea.toFixed(2)) {
                var parentId = (0, core_1.uid)();
                parentObj[crossDirection].push((_d = {
                        id: parentId,
                        parent: parentObj.id,
                        area: parentObj.restArea,
                        width: parentObj.restWidth,
                        height: parentObj.restHeight,
                        restArea: parentObj.restArea,
                        restWidth: direction === "cols" ? parentObj.restWidth - itemWidth : parentObj.restWidth,
                        restHeight: direction === "cols" ? parentObj.restHeight : parentObj.restHeight - itemHeight
                    },
                    _d[direction] = [currentPiece],
                    _d));
                currentPiece["parent"] = parentId;
            }
            else {
                (parentObj["cols"] || parentObj["rows"]).push(currentPiece);
                currentPiece["parent"] = parentObj.id;
                if (direction === "cols") {
                    parentObj.restWidth -= itemWidth;
                }
                else {
                    parentObj.restHeight -= itemHeight;
                }
            }
            if (direction === "cols") {
                nextX += itemWidth;
                nextY = Math.round(parentObj.restArea - itemArea) === 0 ? nextY + itemHeight : nextY;
            }
            else {
                nextY += itemHeight;
                nextX = Math.round(parentObj.restArea - itemArea) === 0 ? nextX + itemWidth : nextX;
            }
        }
        var currentSectorObj = [currentX, currentY, currentPiece.width, currentPiece.height];
        var linkTextObj = [currentX + currentPiece.width / 2, currentY + currentPiece.height / 2];
        return [currentSectorObj, linkTextObj, nextX, nextY, currentPiece.parent];
    };
    TreeMap.prototype._setDefaults = function (config) {
        var defaults = {
            active: true,
            stroke: "#FFFFFF",
            strokeWidth: 2,
            showText: true,
            tooltip: true,
            paddings: 5,
            color: "none",
            fill: "none",
            direction: "desc",
        };
        this.config = __assign(__assign({}, defaults), config);
        this._headerHeight = 25;
        this._aspectRatio = 2.5;
        this._valueLocator = (0, common_1.locator)(config.value);
        this._textLocator = (0, common_1.locator)(config.text);
    };
    TreeMap.prototype._defaultLocator = function (v) {
        return [this._valueLocator(v), this._textLocator(v)];
    };
    TreeMap.prototype._getSerie = function (value, isId) {
        var _this = this;
        var serie = this.config.treeSeries.find(function (item) {
            if (isId) {
                var parentId = _this._data.getParent(value);
                return item.id === parentId || item.id === value;
            }
            else {
                return ((item.from && item.to && value >= item.from && value < item.to) ||
                    (item.less && value < item.less) ||
                    (item.greater && value > item.greater));
            }
        });
        return serie;
    };
    TreeMap.prototype._getDataLevel = function (id) {
        var level = 1;
        this._data.eachParent(id, function () {
            level++;
        });
        return level;
    };
    TreeMap.prototype._getMaxLevel = function () {
        var _this = this;
        var maxLevel = 1;
        this._data.forEach(function (el) {
            var level = _this._getDataLevel(el.id);
            maxLevel = Math.max(level, maxLevel);
        });
        return maxLevel;
    };
    TreeMap.prototype._getDeepParent = function (obj) {
        var _a;
        var child = (_a = (obj["cols"] || obj["rows"])) === null || _a === void 0 ? void 0 : _a.find(function (item) { return item.restArea && Math.round(item.restArea) > 0; });
        return child ? this._getDeepParent(child) : obj;
    };
    TreeMap.prototype._getLayoutObj = function (id, layout) {
        if (layout === void 0) { layout = this._layout; }
        var obj;
        if (layout.id === id) {
            obj = layout;
        }
        else {
            var items = layout["cols"] || layout["rows"];
            if (!items || !items.length)
                return;
            for (var i = 0; i < items.length; i++) {
                obj = this._getLayoutObj(id, items[i]);
                if (obj)
                    break;
            }
        }
        return obj;
    };
    TreeMap.prototype._recountParentArea = function (area, parentId, obj) {
        var parentObj = this._getLayoutObj(parentId, obj);
        if (!parentObj || parentObj.id === obj.parent)
            return;
        parentObj.restArea -= area;
        if (parentObj.parent)
            this._recountParentArea(area, parentObj.parent, obj);
    };
    TreeMap.prototype._toggleGroup = function (id) {
        var _this = this;
        var activeSeries = 0;
        this.config.treeSeries.forEach(function (serie) {
            if (serie.active)
                activeSeries++;
        });
        this._data.eachChild(this._data.getRoot(), function (item) {
            if (item.id === id)
                return;
            if (_this.config.legendType === "groupName") {
                if (activeSeries > 1)
                    _this._getSerie(item.id, true).active = true;
                _this._events.fire(types_1.ChartEvents.toggleSeries, [item.id]);
            }
            else {
                item.$hidden = !item.$hidden;
                _this._events.fire(ts_data_1.DataEvents.change);
            }
        }, false);
    };
    return TreeMap;
}(BaseSeria_1.default));
exports.default = TreeMap;


/***/ }),
/* 77 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
Object.defineProperty(exports, "__esModule", { value: true });
var core_1 = __webpack_require__(0);
var date_1 = __webpack_require__(29);
var dom_1 = __webpack_require__(1);
var ts_data_1 = __webpack_require__(7);
var common_1 = __webpack_require__(2);
var BaseSeria_1 = __webpack_require__(11);
var CalendarHeatMap = /** @class */ (function (_super) {
    __extends(CalendarHeatMap, _super);
    function CalendarHeatMap() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    CalendarHeatMap.prototype.scaleReady = function (sizes) {
        for (var key in sizes) {
            sizes[key] += this.config.paddings;
        }
        return sizes;
    };
    CalendarHeatMap.prototype.dataReady = function () {
        var _this = this;
        var $startDate, $endDate;
        var dataPoints = [];
        this._years = [];
        this._points = [];
        if (this.config.startDate) {
            $startDate =
                typeof this.config.startDate === "string"
                    ? (0, date_1.stringToDate)(this.config.startDate, this.config.dateFormat)
                    : this.config.startDate;
        }
        if (this.config.endDate) {
            $endDate =
                typeof this.config.endDate === "string"
                    ? (0, date_1.stringToDate)(this.config.endDate, this.config.dateFormat)
                    : this.config.endDate;
        }
        if (($endDate === null || $endDate === void 0 ? void 0 : $endDate.getTime()) < ($startDate === null || $startDate === void 0 ? void 0 : $startDate.getTime())) {
            (0, ts_data_1.dhxError)("Incorrect endDate/startDate property set, see docs: https://docs.dhtmlx.com/suite/chart/api/chart_series_config");
        }
        this._data.forEach(function (item) {
            var _a;
            var iValue = Number(_this._valueLocator(item));
            var iDate = _this._dateLocator(item);
            if (typeof iDate === "string") {
                iDate = (0, date_1.stringToDate)(iDate, _this.config.dateFormat);
            }
            var iMs = iDate === null || iDate === void 0 ? void 0 : iDate.getTime();
            var iId = ((_a = item.id) === null || _a === void 0 ? void 0 : _a.toString()) || (0, core_1.uid)();
            if ($startDate || $endDate) {
                var currentMs = date_1.DateHelper.dayStart(iDate).getTime();
                var startMs = ($startDate && date_1.DateHelper.dayStart($startDate).getTime()) || 0;
                var endMs = ($endDate && date_1.DateHelper.dayStart($endDate).getTime()) || Infinity;
                if (currentMs < startMs || currentMs > endMs)
                    return;
            }
            var samePointIndex = dataPoints.findIndex(function (tPoint) {
                var tMs = tPoint[0], tValue = tPoint[1];
                var isSame = date_1.DateHelper.isSameDay(new Date(tMs), iDate);
                if (isSame)
                    iValue += tValue;
                return isSame;
            });
            if (samePointIndex > -1) {
                dataPoints[samePointIndex] = [iMs, iValue, iId];
            }
            if (iValue) {
                _this._minValue = !_this._minValue || iValue < _this._minValue ? iValue : _this._minValue;
                _this._maxValue = !_this._maxValue || iValue > _this._maxValue ? iValue : _this._maxValue;
            }
            samePointIndex < 0 && dataPoints.push([iMs, iValue, iId]);
        });
        dataPoints.sort(function (a, b) { return a[0] - b[0]; });
        if (this._minValue === this._maxValue)
            this._minValue -= 10;
        this._maxValue = (!isNaN(this.config.maxValue) && this.config.maxValue) || this._maxValue;
        this._minValue = (!isNaN(this.config.minValue) && this.config.minValue) || this._minValue;
        this._maxValue = Math.ceil(this._maxValue / 10) * 10;
        this._minValue = Math.floor(this._minValue / 10) * 10;
        var startDateObj = $startDate || new Date(dataPoints[0][0]);
        var endDateObj = $endDate || new Date(dataPoints[dataPoints.length - 1][0]);
        var $startYear = startDateObj.getFullYear();
        var $endYear = endDateObj.getFullYear();
        if (!this.config.startDate && !this.config.endDate) {
            $endYear = endDateObj.getFullYear();
            $startDate = new Date($startYear, 0, 1);
            $endDate = new Date($endYear, 11, 31);
        }
        else if (this.config.startDate && !this.config.endDate) {
            var endDateObjTemp = new Date(startDateObj.getFullYear() + 1, startDateObj.getMonth(), startDateObj.getDate() - 1);
            if (endDateObj.getTime() > endDateObjTemp.getTime()) {
                endDateObjTemp.setFullYear(endDateObj.getFullYear() + 1);
            }
            $endDate = endDateObjTemp;
            $endYear = endDateObjTemp.getFullYear();
        }
        else if (!this.config.startDate && this.config.endDate) {
            $startDate = new Date($startYear, 0, 1);
        }
        var dataPointIndex = 0;
        var tempDate = new Date($startDate.getTime());
        var targetDate = new Date($endDate.getFullYear(), $endDate.getMonth(), $endDate.getDate() + 1);
        while (!date_1.DateHelper.isSameDay(tempDate, targetDate)) {
            var dataPointDate = dataPoints.length && new Date(dataPoints[dataPointIndex][0]);
            var setNextDay = function () { return tempDate.setDate(tempDate.getDate() + 1); };
            if (!dataPoints.length) {
                this._points.push([tempDate.getTime(), 0, (0, core_1.uid)()]);
            }
            else {
                if (dataPointIndex < dataPoints.length - 1) {
                    while (!date_1.DateHelper.isSameDay(tempDate, dataPointDate)) {
                        this._points.push([tempDate.getTime(), 0, (0, core_1.uid)()]);
                        setNextDay();
                    }
                    this._points.push(dataPoints[dataPointIndex]);
                    dataPointIndex += 1;
                }
                else if (date_1.DateHelper.isSameDay(tempDate, dataPointDate)) {
                    this._points.push(dataPoints[dataPointIndex]);
                }
                else {
                    this._points.push([tempDate.getTime(), 0, (0, core_1.uid)()]);
                }
            }
            setNextDay();
        }
        var checkDate = new Date();
        var firstMonth = $startDate.getMonth();
        var firstDay = $startDate.getDate();
        for (var index = 0; index < this._points.length; index++) {
            checkDate.setTime(this._points[index][0]);
            if (index === 0 ||
                (firstMonth === checkDate.getMonth() &&
                    firstDay === checkDate.getDate() &&
                    index !== this._points.length - 1)) {
                this._years.push({
                    year: checkDate.getFullYear(),
                    startIndex: index,
                });
            }
        }
        return this._points;
    };
    CalendarHeatMap.prototype.paint = function (width, height) {
        var _this = this;
        var _a = this.config, days = _a.days, months = _a.months;
        var firstWeekDay;
        var weekDays;
        switch (this.config.weekStart) {
            case "saturday":
                firstWeekDay = -1;
                break;
            case "monday":
                firstWeekDay = 1;
                break;
            default:
                firstWeekDay = 0;
        }
        switch (this.config.weekStart) {
            case "saturday":
                weekDays = __spreadArray([days === null || days === void 0 ? void 0 : days[6]], days.slice(0, -1), true);
                break;
            case "monday":
                weekDays = __spreadArray(__spreadArray([], days.slice(1), true), [days === null || days === void 0 ? void 0 : days[0]], false);
                break;
            default:
                weekDays = days;
        }
        var defs = [];
        var bar = [];
        var svg = [];
        var chartWidth = 0;
        var chartHeight = 0;
        var dashWidth = 1;
        var dashHeight = 8;
        this._years.forEach(function (_a, yearIndex) {
            var _b;
            var year = _a.year, startIndex = _a.startIndex;
            var firstDayOfYear = new Date(year, 0, 1);
            var svgDays = [];
            var svgMonth = [];
            var chartWidthCurrent = 0;
            var pointWeek = 1;
            for (var index = startIndex; index < _this._points.length; index++) {
                if (index === ((_b = _this._years[yearIndex + 1]) === null || _b === void 0 ? void 0 : _b.startIndex))
                    break;
                var point = _this._points[index];
                var ms = point[0], value = point[1], id = point[2];
                var pointDate = new Date();
                pointDate.setTime(ms);
                var pointMonth = pointDate.getMonth();
                var pointDay = date_1.DateHelper.addDay(pointDate, firstWeekDay * -1).getDay();
                if (pointDay === 0 && !date_1.DateHelper.isSameDay(firstDayOfYear, pointDate)) {
                    pointWeek += 1;
                }
                var opacity = value >= 0 ? value / _this._maxValue || 0 : value / _this._minValue || 0;
                if (_this._maxValue * _this._minValue > 0) {
                    opacity = (value - _this._minValue) / (_this._maxValue - _this._minValue);
                }
                var pointX = 10 + pointWeek * (_this._cellSize + 2);
                var pointY = 0.5 +
                    (pointDay + 1) * (_this._cellSize + 2) +
                    yearIndex * (weekDays.length * _this._cellSize + _this._headerHeight);
                if (pointDay === 6) {
                    chartHeight = pointY + _this._cellSize * 4;
                }
                svgDays.push((0, dom_1.sv)("rect", {
                    x: pointX,
                    y: pointY,
                    fill: _this.config.color,
                    width: _this._cellSize,
                    height: _this._cellSize,
                    onclick: [_this._handlers.onclick, value, id],
                    onmousemove: [_this._handlers.onmousemove, id, _this.id],
                    onmouseleave: [_this._handlers.onmouseleave, id, _this.id],
                    class: !_this.config.color ? "heat-neutral" : "",
                }, []));
                var colorClass = "";
                if (value >= 0 && !_this.config.positiveColor) {
                    colorClass = "heat-positive";
                }
                else if (value < 0 && !_this.config.negativeColor) {
                    colorClass = "heat-negative";
                }
                opacity !== 0 &&
                    svgDays.push((0, dom_1.sv)("rect", {
                        x: pointX,
                        y: pointY,
                        fill: value >= 0 ? _this.config.positiveColor : _this.config.negativeColor,
                        "fill-opacity": isFinite(opacity) ? opacity : 0,
                        width: _this._cellSize,
                        height: _this._cellSize,
                        onclick: [_this._handlers.onclick, value, id],
                        onmousemove: [_this._handlers.onmousemove, id, _this.id],
                        onmouseleave: [_this._handlers.onmouseleave, id, _this.id],
                        class: colorClass,
                    }, []));
                chartWidthCurrent = pointX + _this._cellSize;
                if (pointDate.getDate() === 1) {
                    var month = months === null || months === void 0 ? void 0 : months[pointMonth];
                    var xMonth = 10 + pointWeek * (_this._cellSize + 2);
                    var yMonth = _this._cellSize -
                        2 +
                        yearIndex * (weekDays.length * _this._cellSize + _this._headerHeight) +
                        weekDays.length * (_this._cellSize + 2) +
                        4.5;
                    var text = (0, dom_1.sv)("text", {
                        x: xMonth,
                        y: yMonth + dashHeight + 12.5,
                        class: "start-text",
                    }, [month]);
                    var dash = (0, dom_1.sv)("rect", {
                        x: xMonth,
                        y: yMonth,
                        height: dashHeight,
                        width: dashWidth,
                    });
                    svgMonth.push((0, dom_1.sv)("g", { class: "heat-month-text" }, [dash, text]));
                }
            }
            var weekDaysHeader = weekDays.map(function (day, ind) {
                return (0, dom_1.sv)("text", {
                    x: 20,
                    y: 0.5 +
                        (ind + 1) * (_this._cellSize + 2) +
                        _this._cellSize / 2 +
                        yearIndex * (weekDays.length * _this._cellSize + _this._headerHeight),
                    class: "end-text heat-day-text",
                }, [(0, dom_1.sv)("tspan", { dy: "0.5ex" }, [day])]);
            });
            var yearText = (0, dom_1.sv)("text", {
                x: 10 + _this._cellSize + 2,
                y: _this._cellSize -
                    4 +
                    yearIndex * (weekDays.length * _this._cellSize + _this._headerHeight),
                class: "start-text heat-year-text",
            }, [year]);
            bar.push((0, dom_1.sv)("g", {
                id: "".concat(year, "_year"),
            }, [yearText, svgMonth, weekDaysHeader, svgDays]));
            chartWidth = chartWidth > chartWidthCurrent ? chartWidth : chartWidthCurrent;
        });
        var getChartAriaAttrs = function (cfg) { return ({
            "aria-label": "chart ".concat(cfg.text || ""),
        }); };
        svg.push((0, dom_1.sv)("defs", defs));
        svg = svg.concat(bar);
        var chartScale = height / chartHeight < 1 ? height / chartHeight : 1;
        var xPosition = (width - chartWidth * chartScale) / 2;
        return (0, dom_1.sv)("g", __assign(__assign({ id: "seria" + this.config.id }, getChartAriaAttrs(this.config)), { tabindex: 0, transform: "translate(".concat(xPosition > 0 ? xPosition : 0, ", 26) scale(").concat(chartScale, ")") }), svg);
    };
    CalendarHeatMap.prototype._setDefaults = function (config) {
        var defaults = {
            tooltip: true,
            paddings: 5,
            weekStart: "sunday",
            days: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
            months: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        };
        this.config = __assign(__assign({}, defaults), config);
        this._headerHeight = 70;
        this._cellSize = 16;
        this._valueLocator = (0, common_1.locator)(config.value);
        this._dateLocator = (0, common_1.locator)(config.date);
    };
    CalendarHeatMap.prototype._defaultLocator = function (v) {
        return [this._valueLocator(v), (0, date_1.getFormattedDate)("%d/%m/%y", this._dateLocator(v))];
    };
    return CalendarHeatMap;
}(BaseSeria_1.default));
exports.default = CalendarHeatMap;


/***/ }),
/* 78 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
var dom_1 = __webpack_require__(1);
var Stacker = /** @class */ (function () {
    function Stacker() {
        this._series = [];
    }
    Stacker.prototype.add = function (seria) {
        this._series.push(seria);
    };
    Stacker.prototype.dataReady = function (prev) {
        this._toPaint = this._series.filter(function (serie) {
            var next = serie.dataReady(prev);
            if (next.length) {
                prev = next;
                return true;
            }
            return false;
        });
        return prev || [];
    };
    Stacker.prototype.getPoints = function () {
        if (this._toPaint.length) {
            return this._toPaint[0].getPoints().concat(this._toPaint[this._toPaint.length - 1].getPoints());
        }
        return [];
    };
    Stacker.prototype.paint = function (width, height, prev) {
        var svg = [];
        var markers = [];
        this._toPaint.forEach(function (seria) {
            if (seria.paintformAndMarkers) {
                var _a = seria.paintformAndMarkers(width, height, prev), content = _a[0], seriesMarkers = _a[1];
                svg.push(content);
                markers.push(seriesMarkers);
            }
            else {
                var content = seria.paint(width, height, prev);
                svg.push(content);
            }
            prev = seria.getPoints();
        });
        return (0, dom_1.sv)("g", svg.concat(markers));
    };
    return Stacker;
}());
exports.default = Stacker;


/***/ }),
/* 79 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.Tooltip = void 0;
var ts_message_1 = __webpack_require__(80);
var common_1 = __webpack_require__(2);
var line_1 = __webpack_require__(28);
var types_1 = __webpack_require__(9);
var Tooltip = /** @class */ (function () {
    function Tooltip(chart) {
        this._chart = chart;
        this._initEvents();
    }
    Tooltip.prototype.destructor = function () {
        if (this._tooltip) {
            document.body.removeChild(this._tooltip);
            this._tooltip = null;
        }
    };
    Tooltip.prototype._showLineTooltip = function (lineTooltipItems) {
        var lineTooltip = "";
        var root = this._chart.getRootView();
        var calcPointTop = 0;
        for (var _i = 0, lineTooltipItems_1 = lineTooltipItems; _i < lineTooltipItems_1.length; _i++) {
            var item = lineTooltipItems_1[_i];
            calcPointTop += item.top;
            this._prevLine =
                root &&
                    root.refs &&
                    root.refs["line" + Math.round(item.left)] &&
                    root.refs["line" + Math.round(item.left)].el;
            this._prevLine.classList.add("grid-line__active");
            if (!item.text)
                continue;
            var seria = this._chart.getSeries(item.seriaId);
            var point = seria.getPoints();
            if (!point.length || !seria.config.tooltip)
                continue;
            var shadeColor = seria.config.pointColor ||
                (seria.config.color && seria.config.color !== "none"
                    ? seria.config.color
                    : seria.config.fill);
            var shadeHTMLHelper = (0, line_1.getShadeHTMLHelper)(seria.config.pointType !== "empty" && seria.config.pointType
                ? seria.config.pointType
                : "simpleRect", shadeColor);
            lineTooltip += "<div class=\"line-point\" _ref=\"dhx_tooltip_".concat(seria.config.id, "_box\">\n\t\t\t\t<svg class=\"dhx_tooltip_svg\" role=\"graphics-document\" style=\"width: 8px; height: 8px;\">").concat(shadeHTMLHelper(4, 4, point[0][2]), "</svg>\n\t\t\t\t<span class=\"dhx_line-point-text\" _ref=\"dhx_tooltip_").concat(seria.config.id, "_text\">").concat(item.text, "</span>\n\t\t\t</div>");
        }
        if (!lineTooltip)
            return;
        if (!this._tooltip) {
            this._createTooltip();
        }
        var bodyHeight = document.body.offsetHeight;
        var bodyWidth = document.body.offsetWidth;
        this._tooltip.innerHTML = lineTooltip;
        this._tooltip.classList.add("dhx_chart_tooltip__visible");
        var middlePointHeight = calcPointTop / lineTooltipItems.length;
        var lineRect = this._prevLine.getBoundingClientRect();
        var contentHight = this._tooltip.offsetHeight;
        var contentWidth = this._tooltip.offsetWidth;
        var top = middlePointHeight - contentHight / 2 + lineRect.top + window.scrollY;
        var left = lineRect.left + 10;
        if (top + contentHight > bodyHeight) {
            top -= top + contentHight - bodyHeight;
        }
        if (left + contentWidth > bodyWidth) {
            left = lineRect.left - contentWidth - 10;
        }
        var zIndex = (0, ts_message_1.getZIndex)(this._chart.getRootNode());
        if (zIndex) {
            this._tooltip.style.zIndex = zIndex.toString();
        }
        this._tooltip.style.left = left + "px";
        this._tooltip.style.top = top + "px";
    };
    Tooltip.prototype._showTooltip = function (text, e) {
        if (!this._tooltip) {
            this._createTooltip();
        }
        var bodyHeight = document.body.offsetHeight;
        var bodyWidth = document.body.offsetWidth;
        this._tooltip.innerHTML = text;
        this._tooltip.classList.add("dhx_chart_tooltip__visible");
        var contentHight = this._tooltip.offsetHeight;
        var contentWidth = this._tooltip.offsetWidth;
        var mouseTop = e.pageY + 10;
        var mouseLeft = e.pageX + 10;
        if (mouseTop + contentHight > bodyHeight) {
            mouseTop = e.pageY - contentHight;
        }
        if (mouseLeft + contentWidth > bodyWidth) {
            mouseLeft = e.pageX - contentWidth - 10;
        }
        var zIndex = (0, ts_message_1.getZIndex)(this._chart.getRootNode());
        if (zIndex) {
            this._tooltip.style.zIndex = zIndex.toString();
        }
        this._tooltip.style.left = mouseLeft + "px";
        this._tooltip.style.top = mouseTop + "px";
    };
    Tooltip.prototype._showTooltipOnClosest = function (closest) {
        if (!this._tooltip) {
            this._createTooltip();
        }
        var tooltipSeries = this._chart.getSeries(closest[4]);
        if (tooltipSeries) {
            var text = tooltipSeries.getTooltipText(closest[3]);
            if (!text)
                return;
            var layersSize = this._chart._layers.getSizes();
            var root = this._chart.getRootNode();
            var rect = root.getBoundingClientRect();
            this._tooltip.innerHTML = text;
            this._tooltip.classList.add("dhx_chart_tooltip__visible");
            var bodyHeight = document.body.offsetHeight;
            var contentHight = this._tooltip.offsetHeight;
            var top_1 = closest[2] +
                rect.top +
                contentHight / 2 +
                layersSize.top -
                (this._chart.config.type === "radar" ? 10 : 15);
            if (top_1 + contentHight > bodyHeight) {
                top_1 =
                    closest[2] +
                        rect.top -
                        contentHight / 2 +
                        layersSize.top -
                        (this._chart.config.type === "radar" ? 10 : 15);
            }
            if (top_1 + contentHight > bodyHeight) {
                this._tooltip.classList.remove("dhx_chart_tooltip__visible");
            }
            var zIndex = (0, ts_message_1.getZIndex)(this._chart.getRootNode());
            if (zIndex) {
                this._tooltip.style.zIndex = zIndex.toString();
            }
            this._tooltip.style.left = rect.left + closest[1] + 10 + "px";
            this._tooltip.style.top = top_1 + "px";
        }
    };
    Tooltip.prototype._createTooltip = function () {
        this._tooltip = document.createElement("div");
        this._tooltip.setAttribute("data-dhx-widget-id", this._chart._uid);
        this._tooltip.classList.add("dhx_chart_tooltip", "dhx_chart_tooltip_line", "dhx_chart_tooltip__hidden", "tooltip-text");
        document.body.appendChild(this._tooltip);
    };
    Tooltip.prototype._initEvents = function () {
        var _this = this;
        this._chart.events.on(types_1.ChartEvents.chartMouseMove, function (x, y) {
            if (_this._prevLine) {
                _this._prevLine.classList.remove("grid-line__active");
            }
            if (!_this._mouseOverBar) {
                var lineTooltipItems_2 = [];
                var closest_1 = [Infinity, null, null, null, null]; // (dist, x, y, id, serieid)
                _this._chart.eachSeries(function (seria) {
                    var type = seria.config.type;
                    if (type === "line" || type === "spline" || type === "splineArea" || type === "area") {
                        var closestLine = seria.getClosestVertical(x);
                        var item = {
                            value: closestLine[4],
                            text: seria.getTooltipText(closestLine[3]),
                            seriaId: seria.config.id,
                            left: closestLine[1],
                            top: closestLine[2],
                        };
                        if (typeof item.left === "number" && typeof item.top === "number") {
                            lineTooltipItems_2.push(item);
                        }
                    }
                    else if (type === "pie" || type === "pie3D" || type === "donut" || type === "radar") {
                        var seriaClosest = seria.getClosest(x, y);
                        if (closest_1[0] > seriaClosest[0]) {
                            closest_1[0] = seriaClosest[0];
                            closest_1[1] = seriaClosest[1];
                            closest_1[2] = seriaClosest[2];
                            closest_1[3] = seriaClosest[3];
                            closest_1[4] = seria.id;
                        }
                    }
                });
                if (lineTooltipItems_2.length) {
                    lineTooltipItems_2.sort(function (a, b) { return b.value - a.value; });
                    _this._showLineTooltip(lineTooltipItems_2);
                }
                else {
                    _this._showTooltipOnClosest(closest_1);
                }
            }
        });
        this._chart.events.on(types_1.ChartEvents.seriaMouseMove, function (id, seriaId, e) {
            _this._mouseOverBar = true;
            var rootView = _this._chart.getRootView();
            var seria = _this._chart.getSeries(seriaId);
            var text = seria.getTooltipText(id);
            var item = rootView && rootView.refs && rootView.refs[(0, common_1.calcPointRef)(id, seriaId)].el;
            item === null || item === void 0 ? void 0 : item.setAttribute("fill-opacity", seria.config.alpha > 0.6 ? seria.config.alpha - 0.4 : 1);
            if (text) {
                _this._showTooltip(text, e);
            }
            else if (_this._tooltip) {
                _this._tooltip.classList.remove("dhx_chart_tooltip__visible");
            }
        });
        this._chart.events.on(types_1.ChartEvents.seriaMouseLeave, function (id, seriaId) {
            _this._mouseOverBar = false;
            var rootView = _this._chart.getRootView();
            var seria = _this._chart.getSeries(seriaId);
            if (seria.config.type !== "area" &&
                seria.config.type !== "treeMap" &&
                seria.config.type !== "calendarHeatMap") {
                var item = rootView && rootView.refs && rootView.refs[(0, common_1.calcPointRef)(id, seriaId)].el;
                item === null || item === void 0 ? void 0 : item.setAttribute("fill-opacity", seria.config.alpha);
            }
            if (_this._tooltip) {
                _this._tooltip.classList.remove("dhx_chart_tooltip__visible");
            }
        });
        this._chart.events.on(types_1.ChartEvents.chartMouseLeave, function () {
            if (_this._tooltip) {
                _this._tooltip.classList.remove("dhx_chart_tooltip__visible");
            }
            if (_this._prevLine) {
                _this._prevLine.classList.remove("grid-line__active");
            }
        });
    };
    return Tooltip;
}());
exports.Tooltip = Tooltip;


/***/ }),
/* 80 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __exportStar = (this && this.__exportStar) || function(m, exports) {
    for (var p in m) if (p !== "default" && !Object.prototype.hasOwnProperty.call(exports, p)) __createBinding(exports, m, p);
};
Object.defineProperty(exports, "__esModule", { value: true });
__exportStar(__webpack_require__(81), exports);
__exportStar(__webpack_require__(82), exports);
__exportStar(__webpack_require__(83), exports);
__exportStar(__webpack_require__(84), exports);
__exportStar(__webpack_require__(20), exports);


/***/ }),
/* 81 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.message = void 0;
var core_1 = __webpack_require__(0);
var html_1 = __webpack_require__(4);
var types_1 = __webpack_require__(20);
var nodeTimeout = new WeakMap();
var containers = new Map();
function createMessageContainer(parent, position) {
    var messageContainer = document.createElement("div");
    messageContainer.setAttribute("data-position", position);
    messageContainer.className =
        "dhx_message-container " +
            "dhx_message-container--" +
            position +
            (parent === document.body ? " dhx_message-container--in-body" : "");
    return messageContainer;
}
function onExpire(node, fromClick) {
    if (fromClick) {
        clearTimeout(nodeTimeout.get(node));
    }
    var container = node.parentNode;
    var position = container.getAttribute("data-position");
    var parent = container.parentNode;
    var messageContainerInfo = containers.get(parent);
    if (!messageContainerInfo) {
        return;
    }
    var positionInfo = messageContainerInfo[position];
    if (!positionInfo) {
        return;
    }
    var stack = positionInfo.stack;
    var index = stack.indexOf(node);
    if (index !== -1) {
        container.removeChild(node);
        stack.splice(index, 1);
        if (stack.length === 0) {
            parent.removeChild(container);
        }
        return;
    }
}
function message(props) {
    var _a;
    if (typeof props === "string") {
        props = { text: props };
    }
    props.position = props.position || types_1.MessageContainerPosition.topRight;
    var messageBox = document.createElement("div");
    messageBox.className = "dhx_widget dhx_message " + (props.css || "");
    messageBox.setAttribute("role", "alert");
    var textId = props.text && (0, core_1.uid)();
    textId && messageBox.setAttribute("aria-describedby", textId);
    if (props.html) {
        messageBox.innerHTML = props.html;
    }
    else {
        messageBox.innerHTML = "<span class=\"dhx_message__text\" id=".concat(textId, "></span>\n\t\t").concat(props.icon ? "<span class=\"dhx_message__icon dxi ".concat(props.icon, "\"></span>") : "");
        messageBox.querySelector("#".concat(textId)).textContent = props.text;
    }
    var parent = props.node ? (0, html_1.toNode)(props.node) : document.body;
    var position = getComputedStyle(parent).position;
    if (position === "static") {
        parent.style.position = "relative";
    }
    var messageContainerInfo = containers.get(parent);
    if (!messageContainerInfo) {
        containers.set(parent, (_a = {},
            _a[props.position] = {
                stack: [],
                container: createMessageContainer(parent, props.position),
            },
            _a));
    }
    else if (!messageContainerInfo[props.position]) {
        messageContainerInfo[props.position] = {
            stack: [],
            container: createMessageContainer(parent, props.position),
        };
    }
    var _b = containers.get(parent)[props.position], stack = _b.stack, container = _b.container;
    if (stack.length === 0) {
        parent.appendChild(container);
    }
    stack.push(messageBox);
    container.appendChild(messageBox);
    function closeMessage(fromClick) {
        if (fromClick === void 0) { fromClick = true; }
        if (!messageBox)
            return;
        onExpire(messageBox, fromClick);
        messageBox = null;
    }
    if (props.expire) {
        var timeout = setTimeout(function () { return closeMessage(false); }, props.expire);
        nodeTimeout.set(messageBox, timeout);
    }
    messageBox.onclick = function () { return closeMessage(); };
    return {
        close: function () { return closeMessage(); },
    };
}
exports.message = message;


/***/ }),
/* 82 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(Promise) {
Object.defineProperty(exports, "__esModule", { value: true });
exports.alert = void 0;
var en_1 = __webpack_require__(32);
var common_1 = __webpack_require__(33);
var core_1 = __webpack_require__(0);
function alert(props) {
    var apply = props.buttons && props.buttons[0] ? props.buttons[0] : en_1.default.apply;
    var unblock = (0, common_1.blockScreen)(props.blockerCss);
    return new Promise(function (res) {
        var contentId = "dhx_alert__".concat((0, core_1.uid)(), "_content");
        var headerId = "dhx_alert__".concat((0, core_1.uid)(), "_header");
        var alertBox = document.createElement("div");
        alertBox.setAttribute("role", "alert");
        alertBox.setAttribute("aria-modal", "true");
        props.text && alertBox.setAttribute("aria-describedby", contentId);
        props.header && alertBox.setAttribute("aria-labelledby", headerId);
        alertBox.className = "dhx_widget dhx_alert " + (props.css || "");
        var closeAlert = function (e) {
            if (e.key === "Escape" || e.key === "Esc") {
                // eslint-disable-next-line @typescript-eslint/no-use-before-define
                close(e);
                res(false);
            }
        };
        function close(e) {
            e.preventDefault();
            unblock();
            document.body.removeChild(alertBox);
            document.removeEventListener("keydown", closeAlert);
        }
        alertBox.innerHTML = "\n\t\t\t".concat(props.header
            ? "<div id=".concat(headerId, " class=\"dhx_alert__header\"> ").concat(props.htmlEnable !== false ? props.header : "", " </div>")
            : "", "\n\t\t\t").concat(props.text
            ? "<div id=".concat(contentId, " class=\"dhx_alert__content\">").concat(props.htmlEnable !== false ? props.text : "", "</div>")
            : "", "\n\t\t\t<div class=\"dhx_alert__footer ").concat(props.buttonsAlignment ? "dhx_alert__footer--" + props.buttonsAlignment : "", "\">\n\t\t\t\t<button type=\"button\" aria-label=\"confirm\" class=\"dhx_alert__apply-button dhx_button dhx_button--view_flat dhx_button--color_primary dhx_button--size_medium\">").concat(apply, "</button>\n\t\t\t</div>");
        if (props.htmlEnable === false) {
            props.header && (alertBox.querySelector("#".concat(headerId)).textContent = props.header);
            props.text && (alertBox.querySelector("#".concat(contentId)).textContent = props.text);
        }
        document.body.appendChild(alertBox);
        alertBox.querySelector(".dhx_alert__apply-button").focus();
        alertBox.querySelector("button").addEventListener("click", function (e) {
            close(e);
            res(true);
        });
        document.addEventListener("keydown", closeAlert);
    });
}
exports.alert = alert;

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(6)))

/***/ }),
/* 83 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(Promise) {
Object.defineProperty(exports, "__esModule", { value: true });
exports.confirm = void 0;
var en_1 = __webpack_require__(32);
var common_1 = __webpack_require__(33);
var core_1 = __webpack_require__(0);
function confirm(props) {
    props.buttonsAlignment = props.buttonsAlignment || "right";
    var apply = props.buttons && props.buttons[1] ? props.buttons[1] : en_1.default.apply;
    var reject = props.buttons && props.buttons[0] ? props.buttons[0] : en_1.default.reject;
    var unblock = (0, common_1.blockScreen)("dhx_alert__overlay-confirm " + (props.blockerCss || ""));
    return new Promise(function (res) {
        var confirmBox = document.createElement("div");
        confirmBox.setAttribute("role", "alertdialog");
        confirmBox.setAttribute("aria-modal", "true");
        var headerId = props.header && (0, core_1.uid)();
        var textId = props.header && (0, core_1.uid)();
        textId && confirmBox.setAttribute("aria-describedby", textId);
        headerId && confirmBox.setAttribute("aria-labelledby", headerId);
        var focusItem;
        var answer = function (val) {
            unblock();
            // eslint-disable-next-line @typescript-eslint/no-use-before-define
            confirmBox.removeEventListener("click", clickHandler);
            // eslint-disable-next-line @typescript-eslint/no-use-before-define
            document.removeEventListener("keydown", closeConfirm);
            document.body.removeChild(confirmBox);
            res(val);
        };
        var clickHandler = function (e) {
            if (e.target.tagName === "BUTTON") {
                answer(e.target.classList.contains("dhx_alert__confirm-aply"));
            }
        };
        var closeConfirm = function (e) {
            if (e.key === "Escape" || e.key === "Esc") {
                confirmBox.querySelector(".dhx_alert__confirm-aply").focus();
                answer(e.target.classList.contains("dhx_alert__confirm-reject"));
            }
            else if (e.key === "Tab") {
                if (focusItem === "aply") {
                    focusItem = "reject";
                    confirmBox.querySelector(".dhx_alert__confirm-reject").focus();
                }
                else {
                    focusItem = "aply";
                    confirmBox.querySelector(".dhx_alert__confirm-aply").focus();
                }
                e.preventDefault();
            }
        };
        confirmBox.className = "dhx_widget dhx_alert dhx_alert--confirm" + (props.css ? " " + props.css : "");
        confirmBox.innerHTML = "\n\t\t".concat(props.header
            ? "<div class=\"dhx_alert__header\" id=".concat(headerId, "> ").concat(props.htmlEnable !== false ? props.header : "", " </div>")
            : "", "\n\t\t").concat(props.text
            ? "<div class=\"dhx_alert__content\" id=".concat(textId, ">").concat(props.htmlEnable !== false ? props.text : "", "</div>")
            : "", "\n\t\t\t<div class=\"dhx_alert__footer ").concat(props.buttonsAlignment ? "dhx_alert__footer--" + props.buttonsAlignment : "", "\">\n\t\t\t\t<button type=\"button\" aria-label=\"reject\" class=\"dhx_alert__confirm-reject dhx_button dhx_button--view_link dhx_button--color_primary dhx_button--size_medium\">").concat(reject, "</button>\n\t\t\t\t<button type=\"button\"  aria-label=\"aply\"class=\"dhx_alert__confirm-aply dhx_button dhx_button--view_flat dhx_button--color_primary dhx_button--size_medium\">").concat(apply, "</button>\n\t\t\t</div>");
        if (props.htmlEnable === false) {
            props.header && (confirmBox.querySelector("#".concat(headerId)).textContent = props.header);
            props.text && (confirmBox.querySelector("#".concat(textId)).textContent = props.text);
        }
        document.body.appendChild(confirmBox);
        focusItem = "aply";
        confirmBox.querySelector(".dhx_alert__confirm-aply").focus();
        confirmBox.addEventListener("click", clickHandler);
        document.addEventListener("keydown", closeConfirm);
    });
}
exports.confirm = confirm;

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(6)))

/***/ }),
/* 84 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.disableTooltip = exports.enableTooltip = exports.tooltip = exports.getZIndex = exports.findPosition = void 0;
var html_1 = __webpack_require__(4);
var types_1 = __webpack_require__(20);
var DEFAULT_SHOW_DELAY = 750;
var DEFAULT_HIDE_DELAY = 200;
function findPosition(targetRect, position, width, height, margin, recursion) {
    if (margin === void 0) { margin = 8; }
    if (recursion === void 0) { recursion = 0; }
    var pos;
    var left;
    var top;
    if (recursion > 1) {
        position = types_1.Position.center;
    }
    if (position !== "top" && position !== "bottom") {
        var topOffset = targetRect.top + (targetRect.height - height) / 2;
        var isShift = topOffset < 0 && window.pageYOffset + topOffset + height < scrollY + window.innerHeight;
        top = window.pageYOffset + (isShift ? 0 : topOffset);
    }
    switch (position) {
        case types_1.Position.center:
            left = targetRect.left + window.pageXOffset + (targetRect.width - width) / 2;
            if (left + margin < window.pageXOffset) {
                left = targetRect.left + window.pageXOffset;
            }
            pos = types_1.RealPosition.center;
            return { left: left, top: top, pos: pos };
        case types_1.Position.right:
            pos = types_1.RealPosition.right;
            left = targetRect.right + window.pageXOffset + margin;
            if (left + width > window.innerWidth + window.pageXOffset) {
                // // set left
                return findPosition(targetRect, types_1.Position.left, width, height, margin, ++recursion);
            }
            return { left: left, top: top, pos: pos };
        case types_1.Position.left:
            pos = types_1.RealPosition.left;
            left = window.pageXOffset + targetRect.left - width - margin;
            if (left < 0) {
                // // set right
                return findPosition(targetRect, types_1.Position.right, width, height, margin, ++recursion);
            }
            return { left: left, top: top, pos: pos };
        case types_1.Position.top:
            pos = types_1.RealPosition.top;
            left = window.pageXOffset + targetRect.left + (targetRect.width - width) / 2;
            if (left + width > window.innerWidth + window.pageXOffset) {
                left = window.innerWidth + window.pageXOffset - width;
            }
            else if (left < 0) {
                left = 0;
            }
            if (targetRect.top < height) {
                // // set bottom
                return findPosition(targetRect, types_1.Position.bottom, width, height, margin, ++recursion);
            }
            top = window.pageYOffset + targetRect.top - height - margin;
            return { left: left, top: top, pos: pos };
        case types_1.Position.bottom:
        default:
            left = window.pageXOffset + targetRect.left + (targetRect.width - width) / 2;
            if (left + width > window.innerWidth + window.pageXOffset) {
                left = window.innerWidth + window.pageXOffset - width;
            }
            else if (left < 0) {
                left = 0;
            }
            pos = types_1.RealPosition.bottom;
            top = window.pageYOffset + targetRect.bottom + margin;
            if (top + height > window.innerHeight + window.pageYOffset) {
                // // set top
                return findPosition(targetRect, types_1.Position.top, width, height, margin, ++recursion);
            }
            return { left: left, top: top, pos: pos };
    }
}
exports.findPosition = findPosition;
// tooltip init
var tooltipBox = document.createElement("div");
var tooltipText = document.createElement("span");
tooltipText.className = "dhx_tooltip__text";
tooltipBox.appendChild(tooltipText);
tooltipBox.setAttribute("role", "tooltip");
tooltipBox.style.position = "absolute";
var lastNode = null;
var isActive = false;
var hideTimeout = null;
var showTimeout = null;
var activeListenersDestructor;
function getZIndex(node) {
    if (node &&
        ((node.classList.contains("dhx_popup--window") &&
            node.classList.contains("dhx_popup--window_active")) ||
            node.classList.contains("dhx_popup--window_modal") ||
            node.classList.contains("dhx_popup"))) {
        return 10000000;
    }
    if (node === null || node === void 0 ? void 0 : node.classList.contains("dhx_popup--window")) {
        return 9999999;
    }
    if (node && node.offsetParent) {
        return getZIndex(node.offsetParent);
    }
    return null;
}
exports.getZIndex = getZIndex;
function showTooltip(node, text, position, css, force, margin, htmlEnable) {
    if (css === void 0) { css = ""; }
    if (force === void 0) { force = false; }
    if (margin === void 0) { margin = 8; }
    var rects = node.getBoundingClientRect();
    if (htmlEnable) {
        tooltipText.innerHTML = text;
    }
    else {
        tooltipText.textContent = text;
    }
    tooltipBox.style.left = null;
    tooltipBox.style.top = null;
    document.body.appendChild(tooltipBox);
    tooltipBox.className = "dhx_widget dhx_tooltip ".concat(force ? " dhx_tooltip--forced" : "", " ").concat(css);
    var _a = tooltipBox.getBoundingClientRect(), width = _a.width, height = _a.height;
    var _b = findPosition(rects, position, width, height, margin), left = _b.left, top = _b.top, pos = _b.pos;
    var zIndex = getZIndex(node);
    if (zIndex) {
        tooltipBox.style.zIndex = zIndex.toString();
    }
    switch (pos) {
        case types_1.RealPosition.bottom:
            tooltipBox.style.left = left + "px";
            tooltipBox.style.top = top + "px";
            break;
        case types_1.RealPosition.top:
            tooltipBox.style.left = left + "px";
            tooltipBox.style.top = top + "px";
            break;
        case types_1.RealPosition.left:
            tooltipBox.style.left = left + "px";
            tooltipBox.style.top = top + "px";
            break;
        case types_1.RealPosition.right:
            tooltipBox.style.left = left + "px";
            tooltipBox.style.top = top + "px";
            break;
        case types_1.RealPosition.center:
            tooltipBox.style.left = left + "px";
            tooltipBox.style.top = top + "px";
            break;
    }
    tooltipBox.className += " dhx_tooltip--".concat(pos);
    isActive = true;
    if (!force) {
        setTimeout(function () {
            tooltipBox.className += " dhx_tooltip--animate";
        });
    }
}
function hideTooltip(delay) {
    if (lastNode) {
        hideTimeout = setTimeout(function () {
            document.body.removeChild(tooltipBox);
            isActive = false;
            hideTimeout = null;
        }, delay || DEFAULT_HIDE_DELAY);
    }
}
function addListeners(node, text, config) {
    var force = config.force, showDelay = config.showDelay, hideDelay = config.hideDelay, position = config.position, css = config.css, htmlEnable = config.htmlEnable, margin = config.margin;
    if (!force) {
        showTimeout = setTimeout(function () {
            showTooltip(node, text, position || types_1.Position.bottom, css, false, margin, htmlEnable);
        }, showDelay || DEFAULT_SHOW_DELAY);
    }
    var hide = function () {
        if (isActive) {
            hideTooltip(hideDelay);
        }
        clearTimeout(showTimeout);
        node.removeEventListener("mouseleave", hide);
        node.removeEventListener("blur", hide);
        document.removeEventListener("mousedown", hide);
        lastNode = null;
        activeListenersDestructor = null;
    };
    if (force) {
        showTooltip(node, text, position, css, force, margin, htmlEnable);
    }
    node.addEventListener("mouseleave", hide);
    node.addEventListener("blur", hide);
    document.addEventListener("mousedown", hide);
    activeListenersDestructor = hide;
}
// default
function tooltip(text, config) {
    var node = (0, html_1.toNode)(config.node);
    if (node === lastNode) {
        return;
    }
    if (activeListenersDestructor) {
        activeListenersDestructor();
        activeListenersDestructor = null;
    }
    lastNode = node;
    if (hideTimeout) {
        clearTimeout(hideTimeout);
        hideTimeout = null;
        addListeners(node, text, __assign(__assign({}, config), { force: true }));
    }
    else {
        addListeners(node, text, config);
    }
}
exports.tooltip = tooltip;
function _mousemove(e) {
    var node = (0, html_1.locateNode)(e, "dhx_tooltip_text");
    if (!node) {
        return;
    }
    tooltip(node.getAttribute("dhx_tooltip_text"), {
        position: node.getAttribute("dhx_tooltip_position") || types_1.Position.bottom,
        node: node,
    });
}
function enableTooltip() {
    document.addEventListener("mousemove", _mousemove);
}
exports.enableTooltip = enableTooltip;
function disableTooltip() {
    document.removeEventListener("mousemove", _mousemove);
}
exports.disableTooltip = disableTooltip;


/***/ }),
/* 85 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Exporter = void 0;
var html_1 = __webpack_require__(4);
var Exporter = /** @class */ (function () {
    function Exporter(_name, _view) {
        this._name = _name;
        this._view = _view;
        this._version = "9.1.3";
    }
    Exporter.prototype.pdf = function (config) {
        this._rawExport(config, "pdf", this._view);
    };
    Exporter.prototype.png = function (config) {
        this._rawExport(config, "png", this._view);
    };
    Exporter.prototype._rawExport = function (config, mode, view) {
        var _a;
        if (config === void 0) { config = {}; }
        config.url =
            config.url || "https://export.dhtmlx.com/" + this._name + "/" + mode + "/" + this._version;
        if (mode === "pdf") {
            var pdf = config.pdf || {};
            config.pdf = __assign(__assign({}, pdf), { printBackground: false });
        }
        var styles = "";
        var exportStyles = view.config.exportStyles;
        if (typeof config.exportStyles === "boolean" || Array.isArray(config.exportStyles)) {
            exportStyles = config.exportStyles;
        }
        if (exportStyles === true) {
            styles = "".concat((0, html_1.getPageLinksCss)(), "<style>").concat((0, html_1.getPageInlineCss)(), "</style>");
        }
        else if (exportStyles === null || exportStyles === void 0 ? void 0 : exportStyles.length) {
            styles = "".concat((0, html_1.getPageLinksCss)(exportStyles));
        }
        var viewContainer = document.createElement("div");
        viewContainer.setAttribute("style", "display: none;");
        var clone = view.getRootView().node.el.cloneNode(true);
        clone.setAttribute("data-dhx-theme", config.theme || "light");
        viewContainer.appendChild(clone);
        var html = "\n\t\t\t".concat(styles, "\n\t\t\t").concat(viewContainer.innerHTML, "\n\t\t");
        var form = document.createElement("form");
        form.setAttribute("method", "POST");
        form.setAttribute("action", config.url);
        form.innerHTML = "<input type=\"hidden\" name=\"raw\"><input type=\"hidden\" name=\"config\">";
        form.childNodes[0].value = html;
        form.childNodes[1].value = JSON.stringify(config);
        document.body.appendChild(form);
        form.submit();
        (_a = viewContainer.parentNode) === null || _a === void 0 ? void 0 : _a.removeChild(viewContainer);
        setTimeout(function () {
            var _a;
            (_a = form.parentNode) === null || _a === void 0 ? void 0 : _a.removeChild(form);
        }, 100);
    };
    return Exporter;
}());
exports.Exporter = Exporter;


/***/ })
/******/ ]);
});if (window.dhx_legacy) { 
					if (window.dhx){
						for (var key in dhx)
							if (key === 'i18n') {
								for (var lang in dhx[key])
									window.dhx_legacy[key][lang] = dhx[key][lang];
							} else {
								dhx_legacy[key] = dhx[key];
							}
					}
					window.dhx = dhx_legacy; delete window.dhx_legacy;
				}
//# sourceMappingURL=chart.js.map