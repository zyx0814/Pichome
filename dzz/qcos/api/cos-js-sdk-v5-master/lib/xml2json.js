/* Copyright 2015 William Summers, MetaTribal LLC
 * adapted from https://developer.mozilla.org/en-US/docs/JXON
 *
 * Licensed under the MIT License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://opensource.org/licenses/MIT
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
/**
 * @author William Summers
 * https://github.com/metatribal/xmlToJSON
 */
var DOMParser = require('xmldom').DOMParser;

var xmlToJSON = (function () {

    this.version = "1.3.5";

    var options = { // set up the default options
        mergeCDATA: true, // extract cdata and merge with text
        normalize: true, // collapse multiple spaces to single space
        stripElemPrefix: true, // for elements of same name in diff namespaces, you can enable namespaces and access the nskey property
    };

    var prefixMatch = new RegExp(/(?!xmlns)^.*:/);
    var trimMatch = new RegExp(/^\s+|\s+$/g);

    this.grokType = function (sValue) {
        if (/^\s*$/.test(sValue)) {
            return null;
        }
        if (/^(?:true|false)$/i.test(sValue)) {
            return sValue.toLowerCase() === "true";
        }
        if (isFinite(sValue)) {
            return parseFloat(sValue);
        }
        return sValue;
    };

    this.parseString = function (xmlString, opt) {
        if (xmlString) {
            var xml = this.stringToXML(xmlString);
            if (xml.getElementsByTagName('parsererror').length) {
                return null;
            } else {
                return this.parseXML(xml, opt);
            }
        } else {
            return null;
        }
    };

    this.parseXML = function (oXMLParent, opt) {

        // initialize options
        for (var key in opt) {
            options[key] = opt[key];
        }

        var vResult = {},
            nLength = 0,
            sCollectedTxt = "";

        // iterate over the children
        var childNum = oXMLParent.childNodes.length;
        if (childNum) {
            for (var oNode, sProp, vContent, nItem = 0; nItem < oXMLParent.childNodes.length; nItem++) {
                oNode = oXMLParent.childNodes.item(nItem);

                if (oNode.nodeType === 4) {
                    if (options.mergeCDATA) {
                        sCollectedTxt += oNode.nodeValue;
                    }
                } /* nodeType is "CDATASection" (4) */
                else if (oNode.nodeType === 3) {
                    sCollectedTxt += oNode.nodeValue;
                } /* nodeType is "Text" (3) */
                else if (oNode.nodeType === 1) { /* nodeType is "Element" (1) */

                    if (nLength === 0) {
                        vResult = {};
                    }

                    // using nodeName to support browser (IE) implementation with no 'localName' property
                    if (options.stripElemPrefix) {
                        sProp = oNode.nodeName.replace(prefixMatch, '');
                    } else {
                        sProp = oNode.nodeName;
                    }

                    vContent = xmlToJSON.parseXML(oNode);

                    if (vResult.hasOwnProperty(sProp)) {
                        if (vResult[sProp].constructor !== Array) {
                            vResult[sProp] = [vResult[sProp]];
                        }
                        vResult[sProp].push(vContent);

                    } else {
                        vResult[sProp] = vContent;
                        nLength++;
                    }
                }
            }
        }

        if (!Object.keys(vResult).length) {
            // vResult = sCollectedTxt.replace(trimMatch, '') || ''; // by carsonxu 修复 getBucket返回的 Key 是 " /" 这种场景
            vResult = sCollectedTxt || '';
        }

        return vResult;
    };

    // Convert xmlDocument to a string
    // Returns null on failure
    this.xmlToString = function (xmlDoc) {
        try {
            var xmlString = xmlDoc.xml ? xmlDoc.xml : (new XMLSerializer()).serializeToString(xmlDoc);
            return xmlString;
        } catch (err) {
            return null;
        }
    };

    // Convert a string to XML Node Structure
    // Returns null on failure
    this.stringToXML = function (xmlString) {
        try {
            var xmlDoc = null;

            if (window.DOMParser) {

                var parser = new DOMParser();
                xmlDoc = parser.parseFromString(xmlString, "text/xml");

                return xmlDoc;
            } else {
                xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
                xmlDoc.async = false;
                xmlDoc.loadXML(xmlString);

                return xmlDoc;
            }
        } catch (e) {
            return null;
        }
    };

    return this;

}).call({});

var xml2json = function (xmlString) {
    return xmlToJSON.parseString(xmlString);
};

module.exports = xml2json;
