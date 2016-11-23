/*
 *	Tabby jQuery plugin version 0.12
 *
 *	Ted Devito - http://teddevito.com/demos/textarea.html
 *
 *	Copyright (c) 2009 Ted Devito
 *	 
 *	Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following 
 *	conditions are met:
 *	
 *		1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 *		2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer  
 *			in the documentation and/or other materials provided with the distribution.
 *		3. The name of the author may not be used to endorse or promote products derived from this software without specific prior written 
 *			permission. 
 *	 
 *	THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 *	IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE 
 *	LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, 
 *	PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY 
 *	THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT 
 *	OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */
! function(e) {
    function t(e, t, r) {
        var s = e.scrollTop;
        e.setSelectionRange ? i(e, t, r) : document.selection && n(e, t, r), e.scrollTop = s
    }

    function i(e, t, i) {
        var n = e.selectionStart,
            r = e.selectionEnd;
        if (n == r) t ? "	" == e.value.substring(n - i.tabString.length, n) ? (e.value = e.value.substring(0, n - i.tabString.length) + e.value.substring(n), e.focus(), e.setSelectionRange(n - i.tabString.length, n - i.tabString.length)) : "	" == e.value.substring(n, n + i.tabString.length) && (e.value = e.value.substring(0, n) + e.value.substring(n + i.tabString.length), e.focus(), e.setSelectionRange(n, n)) : (e.value = e.value.substring(0, n) + i.tabString + e.value.substring(n), e.focus(), e.setSelectionRange(n + i.tabString.length, n + i.tabString.length));
        else {
            var s = e.value.split("\n"),
                a = new Array,
                o = 0,
                l = 0;
            for (var c in s) l = o + s[c].length, a.push({
                start: o,
                end: l,
                selected: n >= o && l > n || l >= r && r > o || o > n && r > l
            }), o = l + 1;
            var d = 0;
            for (var c in a)
                if (a[c].selected) {
                    var u = a[c].start + d;
                    t && i.tabString == e.value.substring(u, u + i.tabString.length) ? (e.value = e.value.substring(0, u) + e.value.substring(u + i.tabString.length), d -= i.tabString.length) : t || (e.value = e.value.substring(0, u) + i.tabString + e.value.substring(u), d += i.tabString.length)
                }
            e.focus();
            var h = n + (d > 0 ? i.tabString.length : 0 > d ? -i.tabString.length : 0),
                p = r + d;
            e.setSelectionRange(h, p)
        }
    }

    function n(t, i, n) {
        var r = document.selection.createRange();
        if (t == r.parentElement())
            if ("" == r.text)
                if (i) {
                    var s = r.getBookmark();
                    r.moveStart("character", -n.tabString.length), n.tabString == r.text ? r.text = "" : (r.moveToBookmark(s), r.moveEnd("character", n.tabString.length), n.tabString == r.text && (r.text = "")), r.collapse(!0), r.select()
                } else r.text = n.tabString, r.collapse(!1), r.select();
        else {
            var a = r.text,
                o = a.length,
                l = a.split("\r\n"),
                c = document.body.createTextRange();
            c.moveToElementText(t), c.setEndPoint("EndToStart", r);
            var d = c.text,
                u = d.split("\r\n"),
                h = d.length,
                p = document.body.createTextRange();
            p.moveToElementText(t), p.setEndPoint("StartToEnd", r);
            var f = p.text,
                m = document.body.createTextRange();
            m.moveToElementText(t), m.setEndPoint("StartToEnd", c);
            var g = m.text,
                _ = e(t).html();
            e("#r3").text(h + " + " + o + " + " + f.length + " = " + _.length), h + g.length < _.length ? (u.push(""), h += 2, i && n.tabString == l[0].substring(0, n.tabString.length) ? l[0] = l[0].substring(n.tabString.length) : i || (l[0] = n.tabString + l[0])) : i && n.tabString == u[u.length - 1].substring(0, n.tabString.length) ? u[u.length - 1] = u[u.length - 1].substring(n.tabString.length) : i || (u[u.length - 1] = n.tabString + u[u.length - 1]);
            for (var v = 1; v < l.length; v++) i && n.tabString == l[v].substring(0, n.tabString.length) ? l[v] = l[v].substring(n.tabString.length) : i || (l[v] = n.tabString + l[v]);
            1 == u.length && 0 == h && (i && n.tabString == l[0].substring(0, n.tabString.length) ? l[0] = l[0].substring(n.tabString.length) : i || (l[0] = n.tabString + l[0])), h + o + f.length < _.length && (l.push(""), o += 2), c.text = u.join("\r\n"), r.text = l.join("\r\n");
            var y = document.body.createTextRange();
            y.moveToElementText(t), h > 0 ? y.setEndPoint("StartToEnd", c) : y.setEndPoint("StartToStart", c), y.setEndPoint("EndToEnd", r), y.select()
        }
    }
    e.fn.tabby = function(i) {
        var n = e.extend({}, e.fn.tabby.defaults, i),
            r = e.fn.tabby.pressed;
        return this.each(function() {
            $this = e(this);
            var i = e.meta ? e.extend({}, n, $this.data()) : n;
            $this.bind("keydown", function(n) {
                var s = e.fn.tabby.catch_kc(n);
                return 16 == s && (r.shft = !0), 17 == s && (r.ctrl = !0, setTimeout("jQuery.fn.tabby.pressed.ctrl = false;", 1e3)), 18 == s && (r.alt = !0, setTimeout("jQuery.fn.tabby.pressed.alt = false;", 1e3)), 9 != s || r.ctrl || r.alt ? void 0 : (n.preventDefault, r.last = s, setTimeout("jQuery.fn.tabby.pressed.last = null;", 0), t(e(n.target).get(0), r.shft, i), !1)
            }).bind("keyup", function(t) {
                16 == e.fn.tabby.catch_kc(t) && (r.shft = !1)
            }).bind("blur", function(t) {
                9 == r.last && e(t.target).one("focus", function() {
                    r.last = null
                }).get(0).focus()
            })
        })
    }, e.fn.tabby.catch_kc = function(e) {
        return e.keyCode ? e.keyCode : e.charCode ? e.charCode : e.which
    }, e.fn.tabby.pressed = {
        shft: !1,
        ctrl: !1,
        alt: !1,
        last: null
    }, e.fn.tabby.defaults = {
        tabString: String.fromCharCode(9)
    }
}(window.jQuery || window.ender);
var $t = Class.create({
        initialize: function(e) {
            this.id = e || "0", this.children = $H()
        },
        set: function(e, t) {
            this.children.set(e, t)
        },
        get: function(e) {
            return "..." != e ? this.children.get(e) : ""
        }
    }),
    NestedField = Class.create({
        initialize: function(e) {
            this._blank = "...", this.tree = new $H, this.readData(e)
        },
        readData: function(e) {
            delete this.tree, this.tree = new $H, this.third_level = this.second_level = !1, "string" == typeof e ? this.parseString(e) : "object" == typeof e && this.parseObject(e)
        },
        removeTests: function(e) {
            return e.replace(/[\t\r]/g, "").strip()
        },
        testChar: function(e) {
            return /[\t\r]/g.test(e)
        },
        parseObject: function(e) {
            _self = this, e.each(function(e) {
                _self.tree.set(e[0], new $t(e[1])), e[2] && e[2].each(function(t) {
                    _self.tree.get(e[0]).set(t[0], new $t(t[1])), _self.setSecondPresent(), t[2] && t[2].each(function(i) {
                        _self.tree.get(e[0]).get(t[0]).set(i[0], new $t(i[1])), _self.setThirdPresent()
                    })
                })
            })
        },
        parseString: function(e) {
            _self = this, _category = "", _subcategory = "", _item = "", _caseoption = "", e.split("\n").each(function(e) {
                try {
                    if (_caseoption = _self.testChar(e[0]) ? _self.testChar(e[1]) ? 2 : 1 : 0, e = _self.removeTests(e), "" == e) return;
                    switch (_caseoption) {
                        case 0:
                            _self.tree.set(e, new $t), _category = e;
                            break;
                        case 1:
                            _self.tree.get(_category).set(e, new $t), _subcategory = e, _self.setSecondPresent();
                            break;
                        case 2:
                            _self.tree.get(_category).get(_subcategory).set(e, new $t), _self.setThirdPresent()
                    }
                } catch (t) {}
            })
        },
        setThirdPresent: function() {
            this.third_level = !0
        },
        setSecondPresent: function() {
            this.second_level = !0
        },
        getCategory: function() {
            return _categories = [], this.tree.each(function(e) {
                _categories.push("<option value='" + e.value.id + "'>" + e.key + "</option>")
            }), _categories.join()
        },
        getCategoryEscaped: function() {
            return _categories = [], this.tree.each(function(e) {
                _categories.push("<option value='" + escapeHtml(e.value.id) + "'>" + escapeHtml(e.key) + "</option>")
            }), _categories.join()
        },
        getSubcategory: function(e) {
            return _subcategories = [], this.tree.get(e) && this.tree.get(e).children && this.tree.get(e).children.each(function(e) {
                _subcategories.push("<option value='" + e.value.id + "'>" + e.key + "</option>")
            }), _subcategories.first() || (_subcategories = ["<option value='0'>" + this._blank + "</option>"]), _subcategories.join()
        },
        getSubcategoryEscaped: function(e) {
            return _subcategories = [], this.tree.get(e) && this.tree.get(e).children && this.tree.get(e).children.each(function(e) {
                _subcategories.push("<option value='" + e.value.id + "'>" + e.key + "</option>")
            }), _subcategories.first() || (_subcategories = ["<option value='0'>" + this._blank + "</option>"]), _subcategories.join()
        },
        getItems: function(e, t) {
            return _items = [], this.tree.get(e) && this.tree.get(e).get(t).children && this.tree.get(e).get(t).children.each(function(e) {
                _items.push("<option value='" + e.value.id + "'>" + e.key + "</option>")
            }), _items.first() ? _items.join() : !1
        },
        getItemsEscaped: function(e, t) {
            return _items = [], this.tree.get(e) && this.tree.get(e).get(t).children && this.tree.get(e).get(t).children.each(function(e) {
                _items.push("<option value='" + escapeHtml(e.value.id) + "'>" + escapeHtml(e.key) + "</option>")
            }), _items.first() ? _items.join() : !1
        },
        getCategoryList: function() {
            return _categories = [], this.tree.each(function(e) {
                _categories.push(e.key)
            }), _categories
        },
        getSubcategoryList: function(e) {
            return (e && "-1" != e ? this.tree.get(e).children : $H()) || $H()
        },
        getSubcategoryListWithNone: function(e) {
            return ("-1" != e ? this.tree.get(e).children : $H()) || $H()
        },
        getItemsList: function(e, t) {
            return (t && "-1" != t && this.tree.get(e) ? this.tree.get(e).get(t).children : $H()) || $H()
        },
        getItemsListWithNone: function(e, t) {
            return ("-1" != t && "-1" != this.tree.get(e) ? t ? this.tree.get(e).get(t).children : $H() : $H()) || $H()
        },
        toString: function() {
            return _self = this, _treeString = "", _self.tree.each(function(e) {
                _treeString += unescapeHtml(e.key) + "\n", e.value.children.each(function(e) {
                    _treeString += "	" + unescapeHtml(e.key) + "\n", e.value.children.each(function(e) {
                        _treeString += "		" + unescapeHtml(e.key) + "\n"
                    })
                })
            }), _treeString
        },
        toArray: function() {
            return _self = this, _category_array = [], _self.tree.each(function(e) {
                var t = [];
                e.value.children.each(function(e) {
                    var i = [];
                    e.value.children.each(function(e) {
                        i.push([escapeHtml(e.key), escapeHtml(e.value.id)])
                    }), t.push(i.length ? [escapeHtml(e.key), escapeHtml(e.value.id), i] : [escapeHtml(e.key), e.value.id])
                }), _category_array.push(t.length ? [escapeHtml(e.key), escapeHtml(e.value.id), t] : [escapeHtml(e.key), escapeHtml(e.value.id)])
            }), _category_array
        }
    });
! function(e) {
    window.CustomField = function(t, i) {
        this.element = t;
        var n = {
            currentData: {
                disabled_customer_data: {
                    required_for_agent: !1,
                    visible_in_portal: !1,
                    editable_in_portal: !1,
                    editable_in_signup: !1,
                    required_in_portal: !1,
                    required_for_closure: !1
                },
                is_editable: !0
            },
            fieldTemplate: {
                type: "text",
                dom_type: "text",
                label: "Untitled",
                field_type: "custom",
                required_for_agent: !1,
                id: null,
                admin_choices: [],
                field_options: {},
                action: "create",
                is_editable: !0,
                disabled_customer_data: {
                    required_for_agent: !1,
                    visible_in_portal: !1,
                    editable_in_portal: !1,
                    editable_in_signup: !1,
                    required_in_portal: !1,
                    required_for_closure: !1
                }
            },
            fieldMap: {
                field_type: ["custom-type", "value"],
                label: ["custom-label", "value"],
                label_in_portal: ["custom-label-in-portal", "value"],
                required_for_agent: ["agent-required", "checked"],
                visible_in_portal: ["customer-visible", "checked"],
                editable_in_portal: ["customer-editable", "checked"],
                editable_in_signup: ["customer-edit-signup", "checked"],
                required_in_portal: ["customer-required", "checked"],
                validate_using_regex: ["custom-regex-required", "checked"],
                field_options: ["custom-reg-exp", "value"],
                required_for_closure: ["agentclosure", "checked"],
                portalcc: ["portalcc", "checked"],
                portalcc_to: ["portalcc_to", "checked"],
                admin_choices: ["custom-choices", "function"]
            },
            labelDom: ' input[name="custom-label"]',
            regexDom: ' input[name="custom-regex-required"]',
            portalDom: ' input[name="portalcc"]',
            fieldLabel: ".field-label",
            nestedConfig: "#customNestedConfig",
            customerCCOptions: "#cc_to_option",
            dialogContainer: "#CustomFieldsPropsDialog"
        };
        return this.dialogDOMMap = {}, this.settings = e.extend(!0, {}, n, i), this.initialize(), this
    }, CustomField.prototype = {
        initialize: function() {
            e.each(this.settings.fieldMap, e.proxy(function(t, i) {
                this.dialogDOMMap[t] = "admin_choices" == t ? e(this.settings.dialogContainer + " div[name='" + i[0] + "']") : e(this.settings.dialogContainer + " input[name|='" + i[0] + "']")
            }, this))
        },
        getProperties: function() {
            if (e(this.element).data("fresh")) {
                var t = this.settings.fieldTemplate;
                t.field_type = e(this.element).data("field-type"), t.dom_type = e(this.element).data("type"), this.settings.currentData = e.extend({}, t)
            }
            return this.settings.currentData
        },
        setProperties: function() {
            return this.initialize(), this.settings.currentData = $H(this.settings.currentData), this.dialogDOMMap.field_type.val(), e.each(this.settings.fieldMap, e.proxy(function(e, t) {
                if (e in this.settings.fieldTemplate)
                    if ("admin_choices" == e) this.settings.currentData.set(e, this.getAllChoices(this.dialogDOMMap[e]));
                    else if ("field_options" == e) this.setFieldOptions(e, t);
                else if ("field_type" != e) {
                    var i = this.dialogDOMMap[e].prop(t[1]);
                    "label" != e && "label_in_portal" != e || void 0 === i || (i = escapeHtml(i)), this.settings.currentData.set(e, i)
                }
            }, this)), this.setAction(this.settings.currentData, "update"), this.settings.currentData = this.settings.currentData.toObject(), this.settings.currentData
        },
        setAction: function(e, t) {
            switch (t) {
                case "update":
                    "create" != e.get("action") && e.set("action", t);
                    break;
                default:
                    e.set("action", t)
            }
        },
        getAllChoices: function() {
            return []
        },
        setFieldOptions: function(e, t) {
            var i = this.dialogDOMMap.field_type.val(),
                n = {};
            "custom_text" == i ? (regexParts = [], this.dialogDOMMap.validate_using_regex.prop("checked") && (n.regex = {}, regexParts = this.dialogDOMMap[e].prop(t[1]).match(new RegExp("^/(.*?)/([gimy]*)$")), regexParts && regexParts.length > 0 && (n.regex.pattern = escapeHtml(regexParts[1]), n.regex.modifier = regexParts[2]))) : "default_requester" == i && (n.portalcc = this.dialogDOMMap.portalcc.prop("checked"), n.portalcc_to = this.dialogDOMMap.portalcc_to.filter(":checked").val()), this.settings.currentData.set(e, n)
        },
        getValidationRules: function() {
            return this.initialize(), {
                rules: {
                    "custom-label": {
                        required: !0
                    },
                    "custom-label-in-portal": {
                        required: {
                            depends: e.proxy(function() {
                                return this.dialogDOMMap.visible_in_portal.prop("checked")
                            }, this)
                        }
                    },
                    "custom-reg-exp": {
                        required: {
                            depends: e.proxy(function() {
                                return this.dialogDOMMap.validate_using_regex.prop("checked")
                            }, this)
                        },
                        validate_regexp: {
                            depends: e.proxy(function() {
                                return this.dialogDOMMap.validate_using_regex.prop("checked")
                            }, this)
                        }
                    }
                }
            }
        },
        toggleRegexValidation: function(t) {
            e(t).parents("fieldset").next().toggle(t.checked)
        },
        toggleCustomerBehaviorOptions: function(t) {
            var i = e(this.settings.dialogContainer).find("[data-nested-value=" + t.getAttribute("toggle_ele") + "]");
            t.checked && !i.data("disabledByDefault") ? i.children("label").removeClass("disabled").children("input:checkbox").attr("disabled", !1) : i.find("label").addClass("disabled").children("input:checkbox").prop("checked", !1).prop("disabled", !0)
        },
        togglePortalOptions: function(t) {
            e(this.settings.customerCCOptions).toggle(t.checked)
        },
        attachEvents: function() {
            e(document).on("click.dialog-events", ".delete-field", e.proxy(function() {
                return e(this.element).find(".delete-field").trigger("click"), !1
            }, this)), e(document).on("change.dialog-events", this.settings.nestedConfig + " input:checkbox", e.proxy(function(e) {
                return this.toggleCustomerBehaviorOptions(e.target), !1
            }, this)), e(document).on("change.dialog-events", this.settings.dialogContainer + this.settings.regexDom, e.proxy(function(e) {
                return e.stopPropagation(), this.toggleRegexValidation(e.target), !1
            }, this)), e(document).on("change.dialog-events", this.settings.dialogContainer + this.settings.portalDom, e.proxy(function(e) {
                return e.stopPropagation(), this.togglePortalOptions(e.target), !1
            }, this)), e(document).on("keyup.dialog-events", this.settings.dialogContainer + this.settings.labelDom, e.proxy(function(t) {
                return e(this.settings.fieldLabel).text(e(t.target).val()), !1
            }, this))
        }
    }
}(window.jQuery),
function(e) {
    window.customFieldsForm = function(t) {
        var i = {
                ticket: {
                    label_in_portal: "",
                    description: "",
                    active: !0,
                    required: !1,
                    required_for_closure: !1,
                    visible_in_portal: !0,
                    editable_in_portal: !0,
                    required_in_portal: !1,
                    portalcc: !1,
                    portalcc_to: "all",
                    custom_form_type: "ticket"
                },
                contact: {
                    label_in_portal: "",
                    visible_in_portal: !0,
                    editable_in_portal: !0,
                    editable_in_signup: !1,
                    required_in_portal: !1,
                    custom_form_type: "contact"
                },
                company: {
                    custom_form_type: "company"
                }
            },
            n = {
                formContainer: "#custom-field-form",
                customFieldItem: "#custom-fields li",
                fieldValues: "#field_values",
                submitForm: "#Updateform",
                customFieldsWrapper: "#custom-fields",
                saveBtn: ".save-custom-form",
                fieldLabelClass: ".custom-form-label",
                sectionbody: ".section-body",
                dialogContainer: "#CustomFieldsPropsDialog",
                confirmModal: "#ConfirmModal",
                confirmFieldSubmit: "#confirmDeleteSubmit",
                confirmFieldCancel: "#confirmDeleteCancel",
                currentData: null,
                existingFields: {},
                disabledByDefault: [],
                deleteFieldItem: null,
                deleteFieldId: null
            };
        this.settings = e.extend({}, n, t), this.settings.fieldTemplate = i[this.settings.customFormType], this.listSortObject = {}, this.fieldDialog = {}, this.builder_data = [], this.dragField = null, this.position = 1, this.element = null, this.data = $H(), this.section_instance = {}, this.sortSender = null
    }, customFieldsForm.prototype = {
        uniqId: function() {
            return Math.round((new Date).getTime() + 100 * Math.random())
        },
        feedJsonForm: function(t) {
            e(t).each(e.proxy(function(e, t) {
                this.builder_data[t.id] = t.has_section ? "" : this.domCreation(t)
            }, this))
        },
        sectionJsonForm: function(t) {
            e(t).each(e.proxy(function(e, t) {
                t.has_section && (this.builder_data[t.id] = this.domCreation(t))
            }, this))
        },
        domCreation: function(e) {
            var t = jQuery("<li/>"),
                i = this.getFieldClass(e.dom_type, e.field_type),
                n = {
                    currentData: e,
                    fieldTemplate: this.settings.fieldTemplate,
                    customMessages: this.settings.customMessages
                };
            return this.addAdditionalProps(e), data = new window[i](t, n), t.data("customfield", data), this.constructFieldDom(data.getProperties(), t), this.data.set(e.id, data.getProperties()), t
        },
        constructFieldDom: function(t, i) {
            var n = i || jQuery("<li/>");
            if (n.empty().removeClass("field").addClass("custom-field").removeAttr("style"), n.attr("data-id", t.id), n.attr("data-drag-info", t.label), this.setTypefield(t), n.html(JST["custom-form/template/dom_field"](t)).addClass(t.dom_type), t.has_section) {
                var r = this.section_instance.storedSectionData(t.id, t.admin_choices, this.builder_data);
                e.each(r, function(e, t) {
                    n.find(".section-container").prepend(t)
                })
            }
            return n
        },
        reConstructFieldDom: function(t) {
            for (var i = e(this.settings.formContainer).find('li[data-id="' + t.id + '"]'), n = i.length, r = 0; n > r; r++) {
                e(i[r]).find(this.settings.fieldLabelClass).first().html(t.label);
                var s = e(i[r]).find(".private-symbol").first();
                t.visible_in_portal ? s.hide() : s.show()
            }
        },
        appendDom: function(t) {
            e(t).each(e.proxy(function(t, i) {
                i.field_options.section || e(this.settings.formContainer).append(this.builder_data[i.id]), i.has_section && this.section_instance.disableNewSection()
            }, this))
        },
        addAdditionalProps: function(t) {
            this.settings.disabledByDefault[t.field_type] && (t.disabled_customer_data = this.settings.disabledByDefault[t.field_type]), e.inArray(t.field_type, this.settings.nonEditableFields) >= 0 && (t.is_editable = !1), t.custom_form_type = this.settings.customFormType
        },
        setTypefield: function(e) {
            switch (e.dom_type) {
                case "requester":
                    e.type = "text";
                    break;
                case "dropdown_blank":
                case "nested_field":
                    e.type = "dropdown";
                    break;
                case "html_paragraph":
                    e.type = "paragraph";
                    break;
                default:
                    e.type = e.dom_type
            }
        },
        showFieldDialog: function(t) {
            this.element = t;
            var i = e(t).data("id"),
                n = e(t).data("customfield"),
                r = {},
                s = n.getProperties().field_type; - 1 == e.inArray(s, this.settings.nonEditableFields) && (i && this.data.get(i).has_section && (r = this.section_instance.selectedPicklist(i)), this.fieldDialog.show(t, n.attachEvents, r))
        },
        setNewField: function(t, i) {
            if (t.data("fresh")) {
                field_label = t.text(), type = t.data("type"), field_type = t.data("fieldType");
                var n;
                if (type) {
                    var r = t;
                    i && (r = t.clone(), e(this.settings.formContainer).prepend(r), e("body").animate({
                        scrollTop: 0
                    }, "500"));
                    var s = this.getFieldClass(type, field_type),
                        a = {
                            fieldTemplate: this.settings.fieldTemplate,
                            customMessages: this.settings.customMessages
                        };
                    data = new window[s](r, a), n = this.constructFieldDom(data.getProperties(), r, !0), n.data("customfield", data), t.closest("ul").hasClass("section-body") && n.data("section", !0), this.showFieldDialog(n)
                }
            }
        },
        getFieldClass: function(e, t) {
            return "dropdown" == e || "dropdown_blank" == e ? "nested_field" == t ? "CustomNestedField" : "CustomDropdown" : "nested_field" == e ? "CustomNestedField" : "CustomField"
        },
        deleteField: function(t) {
            var i = t.data("id");
            this.settings.deleteFieldItem = t, this.settings.deleteFieldId = i, this.settings.currentData = $H(this.data.get(i)), /^default/.test(this.settings.currentData.get("field_type")) || (e(t).closest("ul").hasClass("section-body") ? this.section_instance.deleteSecFieldsdialog(t, i) : (e(this.settings.dialogContainer).html(JST["custom-form/template/section_confirm"]({
                confirm_type: "deleteNonSecField"
            })), e(this.settings.confirmModal).modal("show")), this.fieldDialog.hideDialog())
        },
        deleteFormField: function(t, i) {
            e(t).attr("data-fresh") ? (e(t).remove(), this.data.unset(i)) : (e(t).hide(), this.setAction(this.data.get(i), "delete"))
        },
        setAction: function(e, t) {
            switch (t) {
                case "update":
                    "create" != e.action && (e.action = t);
                    break;
                default:
                    e.action = t
            }
        },
        saveCustomFields: function(t) {
            t.preventDefault();
            var i = this.getCustomFieldJson();
            e(this.settings.fieldValues).val(i.toJSON()), this.value = e(this).data("commit"), e(this.settings.saveBtn).prop("disabled", !0), e(this.settings.submitForm).trigger("submit")
        },
        getCustomFieldJson: function() {
            var t = $A(),
                i = 0,
                n = !1;
            return e(this.settings.formContainer + " li.custom-field").each(e.proxy(function(r, s) {
                var a = e(s).data("id"),
                    o = e(s).attr("data-fresh") ? !0 : !1,
                    l = this.data.get(a).canpush,
                    c = e.extend({}, this.data.get(a));
                "undefined" == typeof l && (this.data.get(a).canpush = !0, c.has_section && (n = !0), c = e.extend(!0, c, {
                    fresh_field: o
                }), c = this.findFieldPosition(c, s), this.deletePostData(c), t.push(c), delete t[i].canpush, i += 1)
            }, this)), n && this.section_instance.saveSection(), t
        },
        findFieldPosition: function(e) {
            return e.position != this.position && "delete" != e.action && (e.position = this.position, e.fresh_field || (e.action = "edit")), this.position = "delete" == e.action ? this.position : this.position + 1, e
        },
        deletePostData: function(e) {
            return e.custom_field_choices_attributes = e.admin_choices, e.fresh_field && delete e.id, e.field_options && (e.field_options.section || delete e.field_options.section, e.field_options.length < 1 && delete e.field_options), "default" == e.column_name && delete e.custom_field_choices_attributes, delete e.admin_choices, delete e.fresh_field, delete e.dom_type, delete e.validate_using_regex, delete e.disabled_customer_data, delete e.custom_form_type, delete e.is_editable, delete e.has_section, e
        },
        initializeDragDropSortElements: function() {
            e(this.settings.customFieldsWrapper).find(".field").draggable({
                connectToSortable: this.settings.formContainer,
                helper: function() {
                    var t = e(this).clone();
                    return t.find(".dom-icon").removeAttr("title").removeAttr("data-original-title").removeClass("tooltip"), t
                },
                stack: this.settings.customFieldsWrapper + " li",
                revert: "invalid",
                appendTo: "body"
            }), this.initSortableElements(), this.section_instance.initSectionSorting(e(".section-container").find(this.settings.sectionbody)), this.section_instance.sortEventsBind()
        },
        initSortableElements: function() {
            e(this.settings.formContainer).smoothSort({
                revert: !0,
                distance: 5,
                start: e.proxy(function(e, t) {
                    this.sortSender = t.item.parents().first(), t.item.data("fresh") || (this.dragField = t.item)
                }, this),
                sort: e.proxy(function() {
                    null !== this.dragField && this.section_instance.doWhileDrag(this.data.get(this.dragField.data("id")))
                }, this),
                stop: e.proxy(function(t, i) {
                    this.setNewField(i.item), this.dragField = null, e(".default-error-wrap").hide()
                }, this)
            })
        },
        initialize: function() {
            this.feedJsonForm(this.settings.existingFields), this.section_instance = new customSections({
                builder_instance: this,
                formContainer: this.settings.formContainer,
                secCurrentData: this.settings.customSection
            }), this.sectionJsonForm(this.settings.existingFields), this.appendDom(this.settings.existingFields), this.initializeDragDropSortElements(), e(document).on("click.custom-fields", this.settings.customFieldItem, e.proxy(function(t) {
                return this.setNewField(e(t.currentTarget), !0), !1
            }, this)), e(this.settings.formContainer).on("mouseover", "li.custom-field", e.proxy(function(t) {
                e(this.settings.formContainer).hasClass("sort-started") || e(t.currentTarget).find(".options-wrapper").first().show()
            }, this)), e(this.settings.formContainer).on("mouseout", "li.custom-field", function() {
                e(this).find(".options-wrapper").first().hide()
            }), e(this.settings.formContainer).on("click", ".custom-field", e.proxy(function(t) {
                return e(t.currentTarget).hasClass("ui-sortable-helper") || this.showFieldDialog(e(t.currentTarget)), !1
            }, this)), e(this.settings.formContainer).on("click", ".delete-field", e.proxy(function(t) {
                return t.stopPropagation(), this.deleteField(e(t.currentTarget).closest(".custom-field")), !1
            }, this)), e(document).on("click", this.settings.confirmFieldSubmit, e.proxy(function(t) {
                t.stopPropagation(), this.deleteFormField(this.settings.deleteFieldItem, this.settings.deleteFieldId), this.settings.deleteFieldItem = null, this.settings.deleteField = null, e(".options-wrapper").hide(), e(this.settings.confirmModal).modal("hide"), e(".twipsy :visible").hide()
            }, this)), e(document).keypress(this.settings.confirmModal, e.proxy(function(t) {
                t.stopPropagation();
                var i = t.which || t.keyCode || t.charCode;
                13 == i && e(this.settings.confirmFieldSubmit).click()
            }, this)), e(document).on("click", this.settings.confirmFieldCancel, e.proxy(function(t) {
                t.stopPropagation(), e(this.settings.confirmModal).modal("hide")
            }, this)), e(this.settings.saveBtn).on("click", e.proxy(function(e) {
                return this.saveCustomFields(e), !1
            }, this)), e(document).on("show", ".custom-fields-props-dialog.modal", function() {
                setTimeout(function() {
                    e(".modal-body input[type=text]:visible:enabled:first").select().focus()
                }, 500)
            }), e(document).on("customDataChange", e.proxy(function(t, i) {
                var n = e(this.element);
                if (i.id)
                    if (i.has_section || "default_priority" == i.field_type || "default_agent" == i.field_type || "default_group" == i.field_type || "default_product" == i.field_type) this.reConstructFieldDom(i);
                    else {
                        var r = n.parents("li:first");
                        r.hasClass("section") && (i.field_options || (i.field_options = {}), i.field_options.section = !0, n.attr("data-fresh") && this.section_instance.updateSectionFields(i, r)), this.builder_data[i.id] = this.constructFieldDom(i, n)
                    }
                else i.id = this.uniqId(), n.data("section") && (i.field_options || (i.field_options = {}), i.field_options.section = !0, this.section_instance.newSectionFields(i, n)), this.builder_data[i.id] = this.constructFieldDom(i, n);
                return this.data.set(i.id, i), !1
            }, this)), this.fieldDialog = new CustomFieldDialog
        }
    }
}(jQuery),
function(e) {
    "use strict";
    window.CustomFieldDialog = function(t) {
        var i = {
            currentData: null,
            customPropsModal: "#CustomPropsModal",
            dialogContainer: "#CustomFieldsPropsDialog",
            customPropertiesDiv: "#CustomProperties",
            cancelBtn: "#cancel-btn",
            propsSubmitBtn: "#PropsSubmitBtn",
            validateOptions: {}
        };
        this.options = e.extend(!0, {}, i, t), this.element = null, this.instance = null, this.init()
    }, CustomFieldDialog.prototype = {
        init: function() {
            e(document).on("hidden.custom-fields", ".modal", e.proxy(function(t) {
                e(t.target).attr("id") == this.options.customPropsModal.slice(1) && this.closeDialog()
            }, this)), e(document).on("click.custom-fields", this.options.cancelBtn, e.proxy(function() {
                this.hideDialog()
            }, this)), e(document).on("click.custom-fields", this.options.propsSubmitBtn, e.proxy(function() {
                e(this.options.customPropertiesDiv).submit()
            }, this)), e(document).on("keypress.custom-fields", this.options.customPropertiesDiv + " input", e.proxy(function(t) {
                t.stopPropagation();
                var i = t.which || t.keyCode || t.charCode;
                13 == i && (e(this.options.customPropertiesDiv).submit(), e(".twipsy :visible").hide())
            }, this)), e(this.options.customPropertiesDiv).on("submit", function() {
                return !1
            }), this.options.validateOptions = {
                submitHandler: e.proxy(function() {
                    this.options.currentData = this.setCurrentData(), e(this.element).removeClass("active"), e(document).trigger("customDataChange", this.options.currentData), this.hideDialog()
                }, this),
                rules: {},
                messages: {},
                onkeyup: !1,
                onclick: !1,
                ignore: ":hidden"
            }
        },
        show: function(t, i, n) {
            this.element = t, this.instance = e(this.element).data("customfield"), this.options.currentData = this.instance.getProperties(), e(this.element).addClass("active"), e(this.options.dialogContainer).html(JST["custom-form/template/formfield_props"]({
                obj: this.options.currentData,
                picklistIds: n,
                shared_groups: sharedGroups,
                shared_ownership_enabled: shared_ownership_enabled
            })), this.options.validateOptions = e.extend(!0, {}, this.options.validateOptions, e(this.element).data("customfield").getValidationRules()), e(this.options.customPropertiesDiv).validate(this.options.validateOptions), e.proxy(i, this.instance)(), "default_status" === this.options.currentData.field_type && shared_ownership_enabled && e(this.options.customPropsModal).css("width", "760px"), e(this.options.customPropsModal).modal("show")
        },
        setCurrentData: function() {
            return e(this.element).data("fresh", !1), e(this.element).data("customfield").setProperties()
        },
        closeDialog: function() {
            e(this.element).data("fresh") && e(this.element).remove(), e(this.element).removeClass("active"), this.detachEvents()
        },
        detachEvents: function() {
            e(document).off("click.dialog-events").off("keyup.dialog-events").off("change.dialog-events")
        },
        hideDialog: function() {
            e(this.element).removeClass("active"), e(this.options.customPropsModal).modal("hide")
        },
        destroy: function() {
            return this.each(function() {
                e(this).removeData("customFieldDialog")
            })
        }
    }
}(window.jQuery),
function(e) {
    window.CustomDropdown = function(t, i) {
        var n = {
            customMessages: {
                untitled: "Untitled",
                firstChoice: "One",
                secondChoice: "Two",
                noChoiceMessage: "No Choice",
                confirmDelete: "Are you sure you want to delete this?",
                customerLabelEmpty: "Customer Label Missing"
            },
            addChoice: "#addchoice",
            deleteChoice: ".delete_choice_btn",
            maxNoOfChoices: "1000",
            dropdownChoiceDiv: ".custom-choices",
            dropdown_rearrange: ".rearrange-icon"
        };
        return i = e.extend(!0, {}, n, i), CustomField.call(this, t, i), this.initialize(), this
    }, CustomDropdown.prototype = {
        getProperties: function() {
            if (e(this.element).data("fresh")) {
                var t = this.settings.fieldTemplate;
                t.field_type = e(this.element).data("field-type"), t.dom_type = e(this.element).data("type"), t.admin_choices = [{
                    value: this.settings.customMessages.firstChoice,
                    name: this.settings.customMessages.firstChoice
                }, {
                    value: this.settings.customMessages.secondChoice,
                    name: this.settings.customMessages.secondChoice
                }], this.settings.currentData = e.extend({}, t)
            }
            return this.settings.currentData
        },
        getAllChoices: function(t) {
            var i = $A(),
                n = 0;
            return t.find("fieldset").each(function() {
                var t = {
                        value: ""
                    },
                    r = e(this).find("span.dropchoice input[name^='choice_']"),
                    s = e(this).find("input[name='customer_display_name']"),
                    a = e(this).find("input[name='stop_sla_timer']"),
                    o = e(this).find("span.dropchoice select[rel='shared_ownership']"),
                    l = e(this).data("destroy"),
                    c = e(this).data("choice-id");
                t.value = escapeHtml(r.val()), "undefined" != c && 0 != c && (t.id = c), s.length && (t.customer_display_name = escapeHtml(s.val())), a.length && (t.stop_sla_timer = a.prop("checked")), o.length && (t.group_ids = o.val()), t.position = l ? -1 : ++n, t._destroy = l ? 1 : 0, t.name = t.value, "" !== e.trim(t.name) && i.push(t)
            }), i
        },
        deleteChoiceItem: function(e) {
            CustomField.prototype.initialize.call(this);
            var t = this.settings.currentData,
                i = e.parent().data("choice-id");
            if ("default_status" == t.field_type && (2 == i || 3 == i || 4 == i || 5 == i)) return !1;
            if (0 !== e.parent().siblings(":visible").length) {
                var i = e.parent().find("input").attr("data_id");
                0 != i && "undefined" != i ? (e.parent().hide(), e.parent().data("destroy", "1")) : e.parent().remove(), this.toggleMaxLimitErrorMsg(), this.toggleAddChoice()
            }
        },
        addChoiceItem: function(t, i) {
            if (CustomField.prototype.initialize.call(this), i = i || this.dialogDOMMap.admin_choices, t = e.extend(t, {
                    value: "",
                    name: "",
                    customer_display_name: "",
                    stop_sla_timer: !1,
                    field_type: this.settings.currentData.field_type
                }), !this.isMaxLimitReached()) {
                var n = e(JST["custom-form/template/custom_dropdown_choice"]({
                    item: t,
                    shared_groups: sharedGroups,
                    shared_ownership_enabled: shared_ownership_enabled
                }));
                n.appendTo(i), n.find("input[name^='choice_']").focus()
            }
            this.toggleAddChoice(), this.toggleMaxLimitErrorMsg(n)
        },
        isMaxLimitReached: function(e) {
            e = e || this.dialogDOMMap.admin_choices;
            var t = e.find("fieldset:visible").length;
            return t >= this.settings.maxNoOfChoices
        },
        toggleAddChoice: function() {
            e(this.settings.addChoice).toggle(!this.isMaxLimitReached())
        },
        toggleMaxLimitErrorMsg: function(t) {
            this.isMaxLimitReached() ? (t = t || e(this.dialogDOMMap.admin_choices).find("fieldset").last(), e("<div>").addClass("max-item-error error").text(translate.get("maxItemsReached")).appendTo(t)) : e(this.dialogDOMMap.admin_choices).find(".max-item-error").remove()
        },
        attachEvents: function() {
            CustomField.prototype.attachEvents.call(this), e(document).on("click.dialog-events", this.settings.deleteChoice, e.proxy(function(t) {
                return this.deleteChoiceItem(e(t.currentTarget)), !1
            }, this)), e(document).on("click.dialog-events", this.settings.addChoice, e.proxy(function(e) {
                return e.preventDefault(), this.addChoiceItem(), !1
            }, this)), this.initializeChoicesSort()
        },
        initializeChoicesSort: function() {
            e(this.settings.dropdownChoiceDiv).sortable({
                items: "fieldset",
                handle: this.settings.dropdown_rearrange,
                sort: function(t, i) {
                    var n = e(".custom-choices").parents(".modal-body"),
                        r = n.scrollTop(),
                        s = i.position.top - r;
                    50 > s ? n.scrollTop(r - 5) : s > 350 && n.scrollTop(r + 5)
                }
            })
        },
        getValidationRules: function() {
            var t = CustomField.prototype.getValidationRules.call(this),
                i = {
                    rules: {
                        choicelist: {
                            required: {
                                depends: e.proxy(function() {
                                    return "block" == e(this.settings.dropdownChoiceDiv).css("display") ? (choiceValues = "", e.each(e(this.settings.dropdownChoiceDiv).find("fieldset:visible").find("input[name^=choice_]"), function(e, t) {
                                        choiceValues += t.value
                                    }), "" == e.trim(choiceValues)) : !1
                                }, this)
                            },
                            checkCustomerLabel: !0
                        }
                    },
                    messages: {
                        choicelist: {
                            required: this.settings.customMessages.noChoiceMessage
                        }
                    }
                };
            return e.validator.addMethod("checkCustomerLabel", e.proxy(function() {
                return _condition = !0, e.each(e(this.settings.dropdownChoiceDiv).find("fieldset:visible").find("input[name=customer_display_name]"), function() {
                    "" != e.trim(e('input[data-companion="#' + e(this).attr("id") + '"]').val()) && "" == e.trim(e(this).val()) && (_condition = !1)
                }), _condition
            }, this), this.settings.customMessages.customerLabelEmpty), e.extend(!0, {}, t, i)
        }
    }, CustomDropdown.prototype = e.extend({}, CustomField.prototype, CustomDropdown.prototype)
}(window.jQuery), ! function(e) {
    var t = function(t, i) {
        this.originalList = t, this.options = e.extend({}, e.fn.smoothSort.defaults, i, e(t).data()), this.extendOptions(), this.init()
    };
    t.prototype = {
        extendOptions: function() {
            var t = null,
                i = this.options.start;
            this.options.start = function(t, n) {
                "function" == typeof i && i(t, n), e(".ui-sortable").addClass("sort-started")
            };
            var n = this.options.over;
            this.options.over = function(t, i) {
                "function" == typeof n && n(t, i), e(t.target).closest("ul")
            };
            var r = this.options.helper;
            this.options.helper = function(i, n) {
                return "function" == typeof r && r(i, n), t = n.clone().insertAfter(n), e(t).addClass("draggingfield"), n.clone().data("parent", e(this))
            };
            var s = this.options.stop;
            this.options.stop = function(i, n) {
                "function" == typeof s && s(i, n), n.item.addClass("ticket-field-highlight"), t && t.remove(), e(".ui-sortable").removeClass("sort-started"), setTimeout(function() {
                    n.item.removeClass("ticket-field-highlight")
                }, 3e3)
            }
        },
        init: function() {
            e(this.originalList).bind("sortstart", function(t, i) {
                var n = i.item.data("drag-info");
                (null == n || "" == n) && (n = "Move here"), e(".ui-sortable-placeholder").append('<div class="ui-dragging-text">' + n + "</div>")
            }), e(this.originalList).sortable(this.options)
        },
        destroy: function() {
            e(document).off("click.custom-fields"), e(document).off("change.custom-fields")
        }
    }, e.fn.smoothSort = function(i) {
        return this.each(function() {
            var n = e(this),
                r = n.data("smoothSort"),
                s = "object" == typeof i && i;
            r || n.hasClass("ui-sortable") || n.data("smoothSort", r = new t(this, s))
        })
    }
}(window.jQuery),
function(e) {
    window.CustomNestedField = function(t, i) {
        var n = {
            addStatus: "#addstatus"
        };
        return this.nestedTree = new NestedField(""), this.statusChoice = {}, i = e.extend(i, n), CustomDropdown.call(this, t, i), e.extend(this.settings.fieldTemplate, {
            levels: []
        }), this
    }, CustomNestedField.prototype = {
        initializeNestedField: function() {
            e("#nestedTextarea").tabby(), "nested_field" === this.settings.currentData.field_type && (this.nestedTree.readData(this.settings.currentData.admin_choices), e("#nestedTextarea").val(this.nestedTree.toString()), e("#nest-category").html(this.nestedTree.getCategory()), this.onCategoryChange(e("#nest-category")), this.onSubCategoryChange(e("#nest-subcategory")))
        },
        getProperties: function() {
            if (e(this.element).data("fresh")) {
                var t = this.settings.fieldTemplate,
                    i = "category 1 \n	subcategory 1\n		item 1\n		item 2\n	subcategory 2\n		item 1\n		item 2\n	subcategory 3\ncategory 2 \n	subcategory 1\n		item 1\n		item 2\n";
                t.field_type = e(this.element).data("field-type"), t.dom_type = e(this.element).data("type"), t.admin_choices = i, t.label = "", t.label_in_portal = "", t.levels = [{
                    level: 2,
                    label: "",
                    label_in_portal: ""
                }, {
                    level: 3,
                    label: "",
                    label_in_portal: ""
                }], this.settings.currentData = e.extend({}, t)
            }
            return this.settings.currentData
        },
        setProperties: function() {
            return CustomField.prototype.setProperties.call(this), this.getAllLevels(), this.settings.currentData
        },
        getAllChoices: function() {
            return this.nestedTree.readData(e("#nestedTextarea").val()), this.nestedTree.toArray()
        },
        getAllLevels: function() {
            this.settings.currentData = $H(this.settings.currentData), levels = this.settings.currentData.get("levels"), action = this.settings.currentData.get("level_three_present") ? this.nestedTree.third_level ? "edit" : "delete" : "create", levels.length < 2 && levels.push({
                level: 3
            }), this.settings.currentData.get("level_three_present") || this.nestedTree.third_level || levels.pop(), this.settings.currentData.set("levels", levels.map(function(t) {
                return {
                    label: escapeHtml(e("#agentlevel" + t.level + "label").val()),
                    label_in_portal: escapeHtml(e("#customerslevel" + t.level + "label").val()),
                    description: "",
                    level: t.level,
                    id: t.id || null,
                    position: 1,
                    type: "dropdown",
                    action: 3 === t.level ? action : "edit"
                }
            })), this.settings.currentData = this.settings.currentData.toObject()
        },
        showNestedTextarea: function() {
            e("#nestedFieldPreview").slideToggle(), e("#nestedEdit").slideToggle().focus()
        },
        nestedFieldValidation: function() {
            e.validator.addMethod("nestedTree", e.proxy(function(e) {
                return _condition = !0, "nested_field" === this.settings.currentData.field_type && (this.nestedTree.readData(e), _condition = this.nestedTree.second_level), _condition
            }, this), translate.get("nested_tree_validation")), e.validator.addMethod("uniqueNames", e.proxy(function(t, i) {
                return _condition = !0, levels = [1, 2, 3], "nested_field" === this.settings.currentData.field_type && (current_level = e(i).data("level"), levels.each(function(t) {
                    current_level !== t && _condition && (_condition = e("#agentlevel" + t + "label").val().strip().toLowerCase() != e(i).val().strip().toLowerCase())
                })), _condition
            }, this), translate.get("nested_unique_names"))
        },
        getValidationRules: function() {
            var t = CustomField.prototype.getValidationRules.call(this),
                i = {
                    rules: {
                        agentlabel: {
                            required: {
                                depends: function() {
                                    return "none" != e("#NestedFieldLabels").css("display")
                                }
                            },
                            uniqueNames: !0
                        },
                        customerslabel: {
                            required: {
                                depends: function() {
                                    return "none" != e("#NestedFieldLabels").css("display")
                                }
                            }
                        },
                        agentlevel2label: {
                            required: {
                                depends: function() {
                                    return "none" != e("#NestedFieldLabels").css("display")
                                }
                            },
                            uniqueNames: !0
                        },
                        customerslevel2label: {
                            required: {
                                depends: function() {
                                    return "none" != e("#NestedFieldLabels").css("display")
                                }
                            }
                        },
                        agentlevel3label: {
                            required: {
                                depends: e.proxy(function() {
                                    return "none" != e("#NestedFieldLabels").css("display") && this.nestedTree.third_level
                                }, this)
                            },
                            uniqueNames: !0
                        },
                        customerslevel3label: {
                            required: {
                                depends: e.proxy(function() {
                                    return "none" != e("#NestedFieldLabels").css("display") && this.nestedTree.third_level
                                }, this)
                            }
                        },
                        nestedTextarea: {
                            required: {
                                depends: function() {
                                    return "none" != e("#NestedFieldLabels").css("display")
                                }
                            },
                            nestedTree: !0
                        }
                    },
                    messages: {
                        agentlevel3label: {
                            required: translate.get("nested_3rd_level")
                        },
                        customerslevel3label: {
                            required: translate.get("nested_3rd_level")
                        }
                    }
                };
            return this.nestedFieldValidation(), e.extend(!0, {}, t, i)
        },
        backToPreview: function() {
            this.nestedTree.readData(e("#nestedTextarea").val()), e("#nest-category").html(this.nestedTree.getCategoryEscaped()).trigger("change"), setTimeout(this.showNestedTextarea, 200)
        },
        onCategoryChange: function(t) {
            e("#nest-subcategory").html(this.nestedTree.getSubcategoryEscaped(escapeHtml(t.children("option:selected").text()))).trigger("change")
        },
        onSubCategoryChange: function(t) {
            e("#nest-item").html(this.nestedTree.getItemsEscaped(escapeHtml(e("#nest-category option:selected").text()), escapeHtml(t.children("option:selected").text())))
        },
        attachEvents: function() {
            CustomField.prototype.attachEvents.call(this), this.initializeNestedField(), e(document).on("click.dialog-events", "#nested-edit-button", e.proxy(function(t) {
                return t.stopPropagation(), this.showNestedTextarea(), e(".modal-body").animate({
                    scrollTop: e("#nestedEdit").position().top
                }, 200), !1
            }, this)), e(document).on("click.dialog-events", "#nestedDoneEdit", e.proxy(function(t) {
                return t.stopPropagation(), e("#nestedTextarea").valid() && this.backToPreview(), !1
            }, this)), e(document).on("change.dialog-events", "#nest-category", e.proxy(function(t) {
                return t.stopPropagation(), this.onCategoryChange(e(t.target)), !1
            }, this)), e(document).on("change.dialog-events", "#nest-subcategory", e.proxy(function(t) {
                return t.stopPropagation(), this.onSubCategoryChange(e(t.target)), !1
            }, this))
        }
    }, CustomNestedField.prototype = e.extend({}, CustomDropdown.prototype, CustomNestedField.prototype)
}(window.jQuery),
function(e) {
    window.customSections = function(t) {
        var i = {
            type_id: {},
            secCurrentData: {},
            sectionContainer: ".section-container",
            sectionWrapper: ".section-wrapper",
            new_btn: ".new-section",
            newSectionDisabled: "new-section-disabled",
            formContainer: "#custom-field-form",
            customPropsModal: "#CustomPropsModal",
            dialogContainer: "#CustomFieldsPropsDialog",
            sectionSubmitBtn: "#sectionSubmitBtn",
            sectionPropertiesForm: "#sectionProperties",
            sectionFieldValues: "#section_field_values",
            sectionEdit: ".section-title",
            sectionDelete: ".section-delete",
            sectionCancel: "#sectionCancelBtn",
            sectionConfirmModal: "#sectionConfirmModal",
            confirmFieldSubmit: "#confirmFieldSubmit",
            confirmFieldCancel: "#confirmFieldCancel",
            copyHelper: "",
            types: {},
            ui: {},
            selectedPicklistIds: {},
            parent_id: "",
            section_finder: "li.section",
            sortingConnectors: [t.formContainer, ".section-body"].join(",")
        };
        this.options = e.extend({}, i, t), this.section_data = {}, this.all_section_fields = $A(), this.convertHash(), this.init()
    }, customSections.prototype = {
        convertHash: function() {
            var t, i, n = this.options.secCurrentData;
            if ("undefined" != typeof n && null != n)
                for (t = 0; t < n.length; t++)
                    if (this.section_data[n[t].id] = e.extend({}, n[t]), this.section_data[n[t].id].section_fields = {}, n[t].section_fields && n[t].section_fields.length)
                        for (i = 0; i < n[t].section_fields.length; i++) this.section_data[n[t].id].section_fields[n[t].section_fields[i].ticket_field_id] = e.extend({}, n[t].section_fields[i])
        },
        init: function() {
            this.editSectionDialogue(), this.deleteSectionDialogue(), this.sectionValidateOptions(), e(document).on("mouseover", this.options.sectionWrapper, function() {
                var t = e(this).parents("li.custom-field");
                t.find(".options-wrapper").first().hide(), t.addClass("remove-select")
            }), e(this.options.formContainer).on("mouseout", this.options.sectionWrapper, function() {
                e(this).parents("li.custom-field").removeClass("remove-select")
            }), e(this.options.formContainer).on("click", this.options.new_btn, e.proxy(function(t) {
                return t.stopPropagation(), e(this.options.new_btn).hasClass(this.options.newSectionDisabled) || (this.options.parent_id = e(t.currentTarget).closest("li.custom-field").attr("data-id"), this.showSectionDialogue()), !1
            }, this)), e(document).on("click", this.options.sectionCancel, e.proxy(function(e) {
                e.stopPropagation(), this.hideDialog(this.options.customPropsModal)
            }, this)), e(this.options.formContainer).on("click", this.options.sectionWrapper, e.proxy(function(e) {
                e.stopPropagation()
            }, this)), e(document).on("click", this.options.confirmFieldSubmit, e.proxy(function(t) {
                t.stopPropagation();
                var i = e("input[name=moveField]:checked").val();
                switch (e("#confirmType").val()) {
                    case "move":
                        "true" == i ? this.sectionFieldMove("copy") : this.sectionFieldMove("cut");
                        break;
                    case "deleteSecField":
                    case "confirmDeleteField":
                        "true" == i ? this.deleteSecField(!0) : this.deleteSecField(!1);
                        break;
                    case "secToForm":
                        this.sectionToForm(this.options.ui.item.data("id"));
                        break;
                    case "deleteSection":
                        this.deleteSection();
                        break;
                    case "available":
                }
                this.options.ui = {}, e(this.options.sectionConfirmModal).data("isSubmited", !0), e(".options-wrapper").hide(), e(".twipsy :visible").hide(), this.hideDialog(this.options.sectionConfirmModal)
            }, this)), e(document).keypress(this.options.sectionConfirmModal, e.proxy(function(t) {
                t.stopPropagation();
                var i = t.which || t.keyCode || t.charCode;
                13 == i && e(this.options.confirmFieldSubmit).click()
            }, this)), e(document).on("click", this.options.confirmFieldCancel, e.proxy(function(e) {
                e.stopPropagation(), this.cancelSorting(), this.hideDialog(this.options.sectionConfirmModal)
            }, this)), e(document).on("hidden", this.options.sectionConfirmModal, e.proxy(function(t) {
                var i = e(t.currentTarget).data("isSubmited");
                i || this.cancelSorting()
            }, this))
        },
        cancelSorting: function() {
            e(this.options.copyHelper).remove(), e(this.options.ui.sender).sortable("cancel"), this.options.ui = {}, this.options.copyHelper = null
        },
        sortEventsBind: function() {
            e(document).on("sortstop", this.options.formContainer, e.proxy(function(t, i) {
                var n = this.options.builder_instance.sortSender,
                    r = i.item.parents("li");
                if (i.sender = n, n.hasClass("section-body")) r.hasClass("section") ? this.sectionToSectionDialogue(i) : this.sectionToFormDialogue(i);
                else {
                    var s = e(i.item).data("id");
                    if (s) {
                        var a = /^default/.test(this.options.builder_instance.data.get(s).field_type),
                            o = t.target || t.srcElement;
                        !o.hasClassName("field") && r.hasClass("section") && (a ? e(i.sender).sortable("cancel") : this.formToSection(i))
                    }
                }
            }, this))
        },
        doWhileDrag: function(t) {
            var i = e(".ui-sortable-placeholder"),
                n = /^default/.test(t.field_type),
                r = /^default_ticket_type/.test(t.field_type);
            n && !r && e(".default-error-wrap").show(), i.closest("ul").hasClass("section-body") && n ? i.addClass("default-field-error") : i.removeClass("default-field-error")
        },
        initSectionSorting: function(t) {
            var i = this;
            e(t).smoothSort({
                revert: !0,
                items: "li",
                helper: function(t, n) {
                    return i.options.copyHelper = n.clone(!0).insertAfter(n).hide(), n.clone().data("parent", e(this))
                },
                start: function(e, t) {
                    i.options.builder_instance.sortSender = t.item.parents().first()
                },
                stop: function(e, t) {
                    i.options.builder_instance.setNewField(t.item)
                }
            }), e(this.options.formContainer).sortable("option", "connectWith", this.options.sortingConnectors), e(t).sortable("option", "connectWith", this.options.sortingConnectors)
        },
        deleteSectionDialogue: function() {
            e(this.options.formContainer).on("click", this.options.sectionDelete, e.proxy(function(t) {
                var i = this.currentSection(t),
                    n = "deleteSection";
                return e("li[data-section-id=" + i.id + "] .section-body").children(".custom-field:visible").length > 0 && (n = "deleteError"), e(this.options.dialogContainer).html(JST["custom-form/template/section_confirm"]({
                    confirm_type: n,
                    section_id: i.id
                })), e(this.options.sectionConfirmModal).modal("show"), !1
            }, this))
        },
        deleteSection: function() {
            var t = e(this.options.dialogContainer + " input[name=confirm-section-id]").val(),
                i = e(this.options.formContainer).find("[data-section-id='" + t + "']");
            i.data("section-fresh") ? (i.remove(), delete this.section_data[t]) : (i.hide(), this.section_data[t].picklist_ids = [], this.section_data[t].action = "delete"), this.disableNewSection()
        },
        deleteSecFieldsdialog: function(t, i) {
            this.options.ui = t;
            var n = e(this.options.formContainer).find("[data-id = '" + i + "']").length,
                r = 1 == n ? "confirmDeleteField" : "deleteSecField";
            e(this.options.dialogContainer).html(JST["custom-form/template/section_confirm"]({
                confirm_type: r
            })), e(this.options.sectionConfirmModal).modal("show")
        },
        showSectionDialogue: function(t) {
            e.isEmptyObject(t) && (t = {
                label: "",
                picklist_ids: [],
                id: "",
                parent_ticket_field_id: this.options.parent_id
            }), e(this.options.dialogContainer).html(JST["custom-form/template/section_dialogue"]({
                obj: t,
                types: this.mergePicklistSelected(t)
            })), e(this.options.sectionPropertiesForm).validate(this.options.validateOptions), e(this.options.customPropsModal).modal("show")
        },
        editSectionDialogue: function() {
            e(this.options.formContainer).on("click", this.options.sectionEdit, e.proxy(function(t) {
                var i = this.currentSection(t);
                return this.options.parent_id = e(t.currentTarget).closest("li.custom-field").attr("data-id"), this.showSectionDialogue(i), e(t.currentTarget).find(".tooltip").twipsy("hide"), !1
            }, this))
        },
        sectionValidateOptions: function() {
            e.validator.addMethod("uniqueSectionNames", e.proxy(function(t) {
                var i = !0,
                    n = e("input[name = 'section-id']").val();
                return e.each(this.section_data, e.proxy(function(e, r) {
                    r.label.toLowerCase() == escapeHtml(t).toLowerCase() && r.id != n && (i = !1)
                }, this)), i
            }, this), translate.get("unique_section_name")), this.options.validateOptions = {
                submitHandler: e.proxy(function() {
                    this.setSectionData(), this.hideDialog(this.options.customPropsModal)
                }, this),
                rules: {
                    "section-label": {
                        required: !0,
                        uniqueSectionNames: !0
                    }
                },
                messages: {},
                onkeyup: !1,
                onclick: !1
            }
        },
        setSectionData: function() {
            var t = e(this.options.dialogContainer + " select[name=section-type]").val(),
                n = e(this.options.dialogContainer + " input[name=section-id]").val(),
                r = [];
            for (i = 0; i < t.length; i++) picklist_value_ids = {}, picklist_value_ids.picklist_value_id = t[i], r.push(picklist_value_ids);
            var s = {
                label: escapeHtml(e(this.options.dialogContainer + " input[name=section-label]").val()),
                picklist_ids: r,
                action: "save",
                parent_ticket_field_id: this.options.parent_id
            };
            "" == n || null == n ? (s.id = this.options.builder_instance.uniqId(), s.section_fields = {}, this.newSectionData(s)) : (s.id = n, this.editSectionData(s)), this.disableNewSection()
        },
        disableNewSection: function() {
            this.selectedPicklist(this.options.parent_id) < 1 ? e(this.options.new_btn).addClass(this.options.newSectionDisabled) : e(this.options.new_btn).removeClass(this.options.newSectionDisabled)
        },
        newSectionData: function(t) {
            var i = this.constructSection(t);
            this.section_data[t.id] = e.extend({}, t), e("[data-id='" + this.options.parent_id + "']").find(this.options.sectionContainer).prepend(i), e(i).attr("data-section-fresh", !0), this.initSectionSorting(e(i).find(".section-body"))
        },
        checkDeleteIcon: function(t, i, n) {
            i = i ? i : this.options.formContainer, i = n ? e(i) : e(i + " [data-section-id=" + t + "]");
            var r = n ? "li.custom-field" : "li.custom-field:visible",
                s = e(i).find(r),
                a = "ficon-trash-o section-delete",
                o = "ficon-trash-strike-thru tooltip section-disabled-delete",
                l = e(i).find(".section-header > .section-icon");
            l.removeClass(a + " " + o), s.length < 1 ? (l.addClass(a).prop("title", ""), e(i).find(".emptySectionInfo").show()) : (l.addClass(o).prop("title", translate.get("section_has_fields")), e(i).find(".emptySectionInfo").hide())
        },
        editSectionData: function(t) {
            e.extend(this.section_data[t.id], t);
            var i = e(this.options.sectionContainer).find("[data-section-id='" + t.id + "'] .section-header");
            e(i).html(JST["custom-form/template/section_header"]({
                obj: t,
                types: this.options.types[this.options.parent_id]
            })), this.checkDeleteIcon(t.id)
        },
        sectionToSectionDialogue: function(t) {
            this.options.ui = t;
            var i = e(t.item).closest("ul"),
                n = e(t.item).data("id");
            i.parents("li").data("section-id") != t.sender.parents("li").data("section-id") ? t.sender && t.sender.hasClass("section-body") && i.find("li[data-id=" + n + "]:visible").length <= 1 ? (e(this.options.dialogContainer).html(JST["custom-form/template/section_confirm"]({
                confirm_type: "move"
            })), e(this.options.sectionConfirmModal).modal("show")) : (this.cancelSorting(), e(this.options.dialogContainer).html(JST["custom-form/template/section_confirm"]({
                confirm_type: "available"
            })), e(this.options.sectionConfirmModal).modal("show")) : (e(this.options.copyHelper).remove(), this.options.copyHelper = null)
        },
        sectionFieldMove: function(t) {
            var i = e(this.options.ui.item).parents("li").data("section-id"),
                n = e(this.options.ui.sender).closest("li").data("section-id"),
                r = this.options.ui.item.data("id");
            this.section_data[i].section_fields || (this.section_data[i].section_fields = {}), this.section_data[i].action = "save", this.section_data[i].section_fields[r] ? (e("li[data-section-id = " + i + "]").find("[data-id =" + r + " ]:hidden").remove(), delete this.section_data[i].section_fields[r].action) : (this.section_data[i].section_fields[r] = e.extend({}, this.section_data[n].section_fields[r]), delete this.section_data[i].section_fields[r].id), "copy" == t ? e(this.options.copyHelper).show() : (this.deleteSectionFieldDom(e(this.options.copyHelper)), this.deleteSectionField(this.section_data[n], r), this.checkDeleteIcon(n)), this.checkDeleteIcon(i), this.options.copyHelper = null
        },
        sectionToFormDialogue: function(t) {
            this.options.ui = t;
            var i = e(t.item).closest("ul");
            i.hasClass("section-body") || t.sender && t.sender.hasClass("section-body") && (e(this.options.dialogContainer).html(JST["custom-form/template/section_confirm"]({
                confirm_type: "secToForm"
            })), e(this.options.sectionConfirmModal).modal("show"))
        },
        formToSection: function(t) {
            this.options.ui = t;
            var i = e(t.item).parents("li").first().data("section-id"),
                n = this.options.builder_instance.data.get(t.item.data("id")),
                r = {
                    ticket_field_id: n.id,
                    ticket_field_name: n.label,
                    parent_ticket_field_id: this.options.parent_id
                };
            n.field_options.section = !0, this.options.builder_instance.setAction(this.options.builder_instance.data.get(n.id), "update"), this.section_data[i].section_fields || (this.section_data[i].section_fields = {}), this.section_data[i].section_fields[n.id] = e.extend({}, r), this.section_data[i].action = "save", this.checkDeleteIcon(i)
        },
        deleteSecField: function(t) {
            var i = this.options.ui,
                n = i.data("id"),
                r = e(i).parents(this.options.section_finder).data("section-id");
            t ? e.isEmptyObject(this.section_data[r].section_fields[n]) || (this.deleteSectionField(this.section_data[r], n), this.deleteSectionFieldDom(e(i)), this.checkDeleteIcon(r)) : this.removeFromOtherSections(n, !0);
            var s = e("ul.section-body li:visible[data-id=" + n + "]");
            0 === s.length && t === !0 && this.options.builder_instance.deleteFormField(s, n)
        },
        sectionToForm: function(e) {
            delete this.options.builder_instance.data.get(e).field_options.section, this.options.builder_instance.setAction(this.options.builder_instance.data.get(e), "update"), this.removeFromOtherSections(e, !1)
        },
        removeFromOtherSections: function(t, i) {
            var n = e("ul.section-body li[data-id=" + t + "]");
            e.each(this.section_data, e.proxy(function(e, i) {
                i.section_fields[t] && this.deleteSectionField(i, t)
            }, this)), e.each(n, e.proxy(function(t, i) {
                var n = e(i).parents(this.options.section_finder).data("section-id");
                this.deleteSectionFieldDom(e(i)), this.checkDeleteIcon(n)
            }, this)), i && this.options.builder_instance.deleteFormField(n, t)
        },
        deleteSectionField: function(t, i) {
            t.section_fields[i], this.options.builder_instance.data.get(i), e(this.options.ui.item).attr("data-fresh") ? delete t.section_fields[i] : (t.section_fields[i].action = "delete", "delete" != t.action && (t.action = "save"))
        },
        deleteSectionFieldDom: function(e) {
            e.attr("data-fresh") ? e.remove() : e.hide()
        },
        saveSection: function() {
            var t = this;
            e.each(this.section_data, function(i) {
                var n = e(t.options.sectionContainer).find("[data-section-id='" + i + "']"),
                    r = e(n).attr("data-section-fresh") ? !0 : !1,
                    s = t.section_data[i];
                s = e.extend(!0, t.getSectionFields(n, s, i), {
                    fresh_section: r
                }), t.deleteSectionData(s), t.all_section_fields.push(s)
            }), e(this.options.sectionFieldValues).val(this.all_section_fields.toJSON())
        },
        deleteSectionData: function(e) {
            e.fresh_section && delete e.id, delete e.parent_ticket_field_id, delete e.fresh_section
        },
        getSectionFields: function(t, i) {
            var n = $A(),
                r = 1;
            return e(t).find("li.custom-field").each(e.proxy(function(t, s) {
                var a = e(s).data("id"),
                    o = e(s).attr("data-fresh") ? !0 : !1,
                    l = i.section_fields[a];
                l.position != r && (l.position = r, "delete" != i.action && (i.action = "save")), r = "delete" == l.action ? r : r + 1, o && delete l.ticket_field_id, n.push(l)
            }, this)), i.section_fields = n, i
        },
        currentSection: function(t) {
            var i = e(t.currentTarget ? t.currentTarget : t.srcElement).closest("li"),
                n = i.data("section-id");
            return this.section_data[n]
        },
        storedSectionData: function(t, i, n) {
            var r = {},
                s = this;
            return this.options.types[t] = i, this.options.parent_id = t, e.each(this.section_data, function(i, a) {
                if (a.parent_ticket_field_id == t) {
                    r[i] = s.constructSection(a);
                    var o = e.map(a.section_fields, function(e) {
                        return [e]
                    });
                    e.each(o.sort(function(e, t) {
                        return e.position - t.position
                    }), function(e, t) {
                        var s = n[t.ticket_field_id].clone(!0);
                        r[i].find(".section-body").append(s)
                    }), s.checkDeleteIcon(a.id, r[i], "stored")
                }
            }), r
        },
        arrayOfPicklistId: function(e) {
            return e.map(function(e) {
                return parseInt(e.picklist_value_id, 10)
            })
        },
        selectedPicklist: function(t) {
            var i = [];
            return e.each(this.section_data, e.proxy(function(n, r) {
                t == r.parent_ticket_field_id && e.merge(i, this.arrayOfPicklistId(r.picklist_ids))
            }, this)), this.options.types[t].filter(function(e) {
                return i.indexOf(e.id) < 0
            })
        },
        mergePicklistSelected: function(t) {
            var i = this.options.parent_id,
                n = this.arrayOfPicklistId(t.picklist_ids);
            return e.merge(this.selectedPicklist(i), this.options.types[i].filter(function(e) {
                return n.indexOf(e.id) >= 0
            }))
        },
        constructSection: function(e, t) {
            var t = t || jQuery("<li/>");
            return t.empty().addClass("section").attr("data-section-id", e.id).html(JST["custom-form/template/section"]({
                obj: e,
                types: this.options.types[e.parent_ticket_field_id]
            })), t
        },
        newSectionFields: function(t, i) {
            var n = i.parents("li").data("section-id"),
                r = {
                    ticket_field_id: t.id,
                    ticket_field_name: t.label,
                    parent_ticket_field_id: this.options.parent_id
                };
            e.isEmptyObject(this.section_data[n].section_fields) && (this.section_data[n].section_fields = {}), this.section_data[n].section_fields[t.id] = e.extend({}, r), this.checkDeleteIcon(n)
        },
        updateSectionFields: function(e, t) {
            var i = t.data("section-id");
            this.section_data[i].section_fields[e.id].ticket_field_name = e.label
        },
        hideDialog: function(t) {
            e(t).modal("hide")
        }
    }
}(jQuery),
function(e) {
    window.TicketFieldsForm = function(e) {
        return customFieldsForm.call(this, e), this
    }, TicketFieldsForm.prototype = {
        deletePostData: function(t) {
            return t = customFieldsForm.prototype.deletePostData.call(this, t), t.choices = t.custom_field_choices_attributes || [], delete t.custom_field_choices_attributes, e.each(t.choices, function(e, i) {
                "default_status" == t.field_type ? (i.status_id = i.id, i.deleted = i._destroy ? !0 : !1, i.name = i.value, delete i.value, delete i._destroy, delete i.id, delete i.field_type) : delete i.name
            }), "update" == t.action && (t.action = "edit"), delete t.portalcc, delete t.portalcc_to, delete t.dom_type, delete t.level_three_present, t.required = t.required_for_agent, delete t.required_for_agent, delete t.name, ("default_ticket_type" == t.field_type || "custom_dropdown" == t.field_type) && (t.picklist_values_attributes = t.choices), t
        }
    }, TicketFieldsForm.prototype = e.extend({}, customFieldsForm.prototype, TicketFieldsForm.prototype), e(document).ready(function() {
        e.each(customFields, function(t, i) {
            i.admin_choices = i.admin_choices || i.choices || [];
            var n = [];
            e.each(i.admin_choices, function(t, r) {
                var s = {};
                e.isArray(r) && "nested_field" != i.field_type ? (s.name = r[0], s.id = r[2] || r[1], s._destroy = r.deleted || !1, s.value = s.name) : (s = e.extend({}, s, r), s.id = s.status_id, s._destroy = s.deleted || !1, s.value = s.name), n.push(s)
            }), "nested_field" != i.field_type && (i.admin_choices = n), i.required_for_agent = i.required
        }), ticketField = new TicketFieldsForm({
            existingFields: customFields,
            customMessages: tf_lang,
            customFormType: "ticket",
            customSection: customSection
        }), ticketField.initialize()
    })
}(jQuery),
function() {
    this.JST || (this.JST = {}), this.JST["custom-form/template/agent_behavior"] = function(obj) {
        var __p = [];
        with(obj || {}) __p.push("<label class='caption'> ", translate.get("forAgent"), " </label>\n<div>\n	"), "ticket" == custom_form_type && "default_requester" == field_type ? __p.push('\n		<fieldset> \n			<label class="checkbox disabled"><input type="checkbox" disabled="true" name="cc_field" checked/> ', translate.get("displayCCField"), " </label>\n		</fieldset> \n	") : (__p.push("\n		<fieldset> \n			"), isDisabled = " ", closeTicketDisable = " ", __p.push("\n			"), ("default_email" == field_type || "default_twitter_id" == field_type || "default_company" == field_type) && (isDisabled = " disabled"), __p.push("\n			 "), ("default_email" == field_type || "default_twitter_id" == field_type) && (closeTicketDisable = " disabled"), __p.push('\n			<label class="checkbox', isDisabled, '">\n				<input type="checkbox" \n								name="agent-required" \n								', required_for_agent ? "checked " : " ", "\n								", isDisabled, " />\n				", translate.get("agentMandatory"), " \n			</label>\n		</fieldset>\n		"), "ticket" == custom_form_type && "default_internal_agent" != obj.field_type && "default_internal_group" != obj.field_type && __p.push('\n			<fieldset>\n				<label class="checkbox', closeTicketDisable, '">\n					<input type="checkbox" \n							name="agentclosure" \n							', required_for_closure ? "checked " : " ", "\n							", closeTicketDisable, " >\n					", translate.get("agent_mandatory_closure"), "\n				</label>\n			</fieldset>\n		"), __p.push("\n	")), __p.push("\n</div>\n");
        return __p.join("")
    }
}.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["custom-form/template/custom_dropdown_choice"] = function(obj) {
            var __p = [];
            with(obj || {}) __p.push(""), isDeleted = item._destroy ? !0 : !1, __p.push("\n"), deletedClassName = isDeleted ? "hide" : "", __p.push("\n"), isDisabled = "delete_choice_btn", __p.push("\n"), disabledTooltip = translate.get("dropdown_choice_disabled"), __p.push("\n\n"), "default_status" == item.field_type && (isDisabled = 2 == item.id || 3 == item.id || 4 == item.id || 5 == item.id ? "disabled tooltip" : "delete_choice_btn"), item.has_section && item.id && (disabledTooltip = translate.get("remove_type"), isDisabled = 0 == picklistIds.filter(function(e) {
                return e.id == item.id
            }).length ? "disabled tooltip" : "delete_choice_btn"), __p.push("\n<fieldset class='", deletedClassName, "'\n			data-destroy=\"", isDeleted, '"\n			data-choice-id="', item.id ? item.id : "0", '">\n	<span class="rearrange-icon ficon-rearrange"></span>\n	<span class=\'ficon-minus delete-choice ', isDisabled, "'\n		"), "delete_choice_btn" != isDisabled && __p.push('\n			title="', disabledTooltip, '"\n		'), __p.push('\n	></span>\n	<span class="dropchoice">\n		<input type="text" \n				name="choice_', item.id || (new Date).getTime(), '" \n				'), "default_status" == item.field_type && __p.push('	\n				rel="companion"\n				data-companion="#statusCustomerName_', item.id || (new Date).getTime(), '" \n				'), __p.push('\n				value="', item.name.replace(/"/g, "&quot;"), "\"\n				class='field_maxlength'\n				maxlength='255'\n				", isDisabled, "/>\n	</span>\n	"), "default_status" == item.field_type && __p.push('\n		<span class="dropchoice">\n			<input type="text" \n					id="statusCustomerName_', item.id || (new Date).getTime(), '"\n					name="customer_display_name" \n					value="', item.customer_display_name.replace(/"/g, "&quot;"), '" \n					placeholder = ""/>\n		</span>\n	'), __p.push("\n	"), "default_status" == item.field_type && 2 != item.id && 4 != item.id && 5 != item.id && (__p.push('\n		<input rel="toggle" type="checkbox" \n				name="stop_sla_timer" \n				data-inverted=true \n				'), item.stop_sla_timer && __p.push(' checked="checked" '), __p.push(" \n				value=true />\n	")), __p.push("\n	"), "delete_choice_btn" === isDisabled && "default_status" == item.field_type && shared_ownership_enabled && (__p.push('\n\n		<span class="dropchoice customdropchoice">\n			<select class="select2" multiple="true" name="group_ids" rel="shared_ownership" data="', item.group_ids, '">\n				'), _.each(shared_groups, function(e) {
                __p.push('\n					<option value="', e[0], '">', e[1], " </option>\n				")
            }), __p.push("\n			</select>\n		</span>\n	")), __p.push('\n\n</fieldset>\n<script type="text/javascript">\n	jQuery(document).ready(function(){\n		jQuery(\'[rel=shared_ownership]\').livequery(function(){\n			var hash_val = [];\n			var _this = jQuery(this);\n			if(_this.attr("data").length > 0){\n				_this.attr("data").split(",").each(function(item, i){ hash_val.push(item); });\n				_this.val(hash_val);\n			}\n		});\n	});\n</script>\n');
            return __p.join("")
        }
    }.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["custom-form/template/custom_dropdown"] = function(obj) {
            var __p = [];
            with(obj || {}) __p.push("<div class=\"section colsets\">\n	<h3 class='title'> ", translate.get("dropdownChoice"), ' </h3>\n	<div name="custom-choices" class="custom-choices" id="custom_choices">\n		'), "default_status" == obj.field_type && (__p.push('\n			<div class="status_dropdown">\n				<span>', translate.get("forAgent"), "</span>\n				<span>", translate.get("forCustomer"), "</span>\n				<span>", translate.get("sla_timer"), "</span>\n				"), shared_ownership_enabled && __p.push("\n					<span>", translate.get("mappedInternalGroup"), "</span>\n				"), __p.push("\n			</div>\n		")), __p.push("\n		"), _.each(obj.admin_choices, function(e) {
                __p.push("\n			", JST["custom-form/template/custom_dropdown_choice"]({
                    item: _.extend(e, {
                        field_type: obj.field_type,
                        has_section: obj.has_section
                    }),
                    picklistIds: picklistIds,
                    shared_groups: shared_groups,
                    shared_ownership_enabled: shared_ownership_enabled
                }), "\n		")
            }), __p.push('\n	</div>\n	<input type="text" name="choicelist" value="" />\n</div> \n');
            return __p.join("")
        }
    }.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["custom-form/template/custom_field_label"] = function(obj) {
            var __p = [];
            with(obj || {}) __p.push('<h3 class="title">', translate.get("label"), '</h3>\n<div class="row-fluid">\n	'), isDisabled = isDefault ? "disabled" : "", __p.push('\n	<fieldset class="', spanClass, '">\n		<input type="text" \n						placeholder="', translate.get("labelAgent"), '" \n						rel="companion" \n						data-companion="#customlabelportal" \n						name="custom-label" \n						value="', label.replace(/"/g, "&quot;"), '"\n						class="', isDisabled, '" \n						', isDisabled, " />\n	</fieldset>\n	"), isContactEmail || "company" == custom_form_type || __p.push('\n		<fieldset class="span6">\n			<input type="text" \n							placeholder="', translate.get("labelCustomer"), '" \n							name="custom-label-in-portal" \n							id="customlabelportal" \n							value="', label_in_portal.replace(/"/g, "&quot;"), '" />\n		</fieldset>\n	'), __p.push("\n</div>\n");
            return __p.join("")
        }
    }.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["custom-form/template/custom_modal_header"] = function(obj) {
            var __p = [];
            with(obj || {}) __p.push('<h3 class="ellipsis modal-title pull-left">\n			'), icon = "ficon-" + ("email" == dom_type ? "mail" : "requester" == dom_type ? "text" : dom_type), __p.push("\n			<span class= '", icon, " dom-icon tooltip' title='", translate.get(dom_type), "'></span>\n			", translate.get("formTitle") + " : ", " \n			<span class='field-label'>", label, "</span>\n		</h3>\n		"), isDefault && __p.push("\n			<span class='label label-light pull-right default-tag'> ", translate.get("default"), " </span>\n		"), __p.push("\n");
            return __p.join("")
        }
    }.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["custom-form/template/custom_text"] = function(obj) {
            var __p = [];
            with(obj || {}) __p.push("<div class='section'>\n	"), regexValidation = null != field_options && "object" == typeof field_options && field_options.regex ? "checked" : "", __p.push("\n	"), regexValue = "checked" == regexValidation ? new RegExp(unescapeHtml(field_options.regex.pattern).replace(/"/g, "&quot;"), field_options.regex.modifier).toString() : "", __p.push('\n	<fieldset> \n		<label class="checkbox', isDisabled, '">\n			<input type="checkbox" \n							name="custom-regex-required" \n							', regexValidation, " />\n			", translate.get("validateRegex"), " \n		</label>\n	</fieldset>\n	"), isVisible = "hide", "checked" == regexValidation && (isVisible = "show"), __p.push('\n	<div class="row-fluid ', isVisible, '">\n		<fieldset class="span12">\n			<input type="text" \n							placeholder="', translate.get("regExp"), '"  \n							name="custom-reg-exp" \n							value="', regexValue, '" />\n			<p> ', translate.get("regExpExample"), "\n				<a href='http://rubular.com/' target='_blank'>", translate.get("learnMore"), " </a>\n			</p>\n		</fieldset>\n	</div>\n</div>\n");
            return __p.join("")
        }
    }.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["custom-form/template/customer_behavior_requester"] = function(obj) {
            var __p = [];
            with(obj || {}) __p.push("<label class='caption'> ", translate.get("forCustomer"), ' </label>\n<div id="field_options">\n	<div id="req_field_options">\n	<fieldset> \n		<label class="checkbox"><input type="checkbox" name="portalcc"  id="portalcc_id" ', field_options && field_options.portalcc ? "checked" : "", " />", translate.get("displayCCField"), ' </label>\n	</fieldset>   \n	<div id="cc_to_option" class="', field_options && field_options.portalcc ? "" : "hide", '">\n		<fieldset class="tabbed" > \n			<label class="radio"><input type="radio" name="portalcc_to"  value="company" ', field_options && "company" == field_options.portalcc_to ? "checked" : "", "/>", translate.get("ccCompanyContacts"), ' </label>\n			<label class="radio"><input type="radio" name="portalcc_to" value="all" ', field_options && "all" == field_options.portalcc_to ? "checked" : "", " />", translate.get("ccAnyEmail"), " </label>\n		</fieldset> \n	</div>\n</div>\n");
            return __p.join("")
        }
    }.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["custom-form/template/customer_behavior"] = function(obj) {
            var __p = [];
            with(obj || {}) __p.push("<label class='caption ", hideIfEmail, "'> ", translate.get("forCustomer"), ' </label>\n<div>\n	<fieldset id="customNestedConfig">\n		'), isDisabled = disabled_customer_data.visible_in_portal || "default_company" == field_type ? "disabled" : "", __p.push('\n		<label class="checkbox ', isDisabled, '">\n			'), isContactEmail ? __p.push("\n				<span class='ficon-checkmark-thick chkbox-img'></span>\n			") : __p.push(' \n				<input type="checkbox" \n								toggle_ele="nestedlvl2" \n								', visible_in_portal ? "checked " : " ", '\n								name="customer-visible"\n								', isDisabled, "/>\n			"), __p.push("\n			", translate.get("customerVisible"), ' \n		</label>\n		<fieldset data-nested-value="nestedlvl2" class="nestedChoices" data-disabled-by-default="', disabled_customer_data.editable_in_portal, '">\n			'), isDisabled = disabled_customer_data.editable_in_portal || !visible_in_portal || "default_company" == field_type ? " disabled" : " ", __p.push('\n			<label class="checkbox ', isDisabled, " ", hideIfEmail, '">\n				<input type="checkbox" \n								toggle_ele="nestedlvl3" \n								name="customer-editable" \n								', visible_in_portal && editable_in_portal ? "checked " : " ", "\n								", isDisabled, " />\n				", translate.get("customerEditable"), " \n			</label>\n			"), "contact" == custom_form_type && (__p.push(' \n				<fieldset data-nested-value="nestedlvl3" class="nestedChoices" data-disabled-by-default="', disabled_customer_data.editable_in_signup, '">\n					'), isDisabled = !disabled_customer_data.editable_in_signup && visible_in_portal && editable_in_portal ? " " : " disabled", __p.push('\n					<label class="checkbox ', isDisabled, '">\n						'), isContactEmail ? __p.push("\n							<span class='ficon-checkmark-thick chkbox-img'></span>\n						") : __p.push(' \n							<input type="checkbox" \n											name="customer-edit-signup" \n											', visible_in_portal && editable_in_portal && editable_in_signup ? "checked " : " ", "\n											", isDisabled, "/>\n						"), __p.push("\n						", translate.get("customerEditSignup"), " \n					</label>\n				</fieldset>\n			")), __p.push('\n			<fieldset data-nested-value="nestedlvl3" class="nestedChoices ', hideIfEmail, '" data-disabled-by-default="', disabled_customer_data.required_in_portal, '">\n				'), isDisabled = !disabled_customer_data.required_in_portal && visible_in_portal && editable_in_portal && "default_company" != field_type ? " " : " disabled", __p.push('\n				<label class="checkbox ', isDisabled, '">\n					<input type="checkbox" \n									name="customer-required" \n									', visible_in_portal && editable_in_portal && required_in_portal ? "checked " : " ", "\n									", isDisabled, " />\n					", translate.get("customerMandatory"), " \n				</label>\n			</fieldset>\n		</fieldset>\n	</fieldset>  \n</div>\n");
            return __p.join("")
        }
    }.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["custom-form/template/dom_field"] = function(obj) {
            var __p = [];
            with(obj || {}) {
                __p.push("");
                var isDefault = /^default/.test(field_type),
                    delete_icon = isDefault ? "ficon-trash-strike-thru" : "ficon-trash-o",
                    edit_icon = is_editable ? "ficon-edit" : "ficon-edit-strike-thru",
                    delete_tooltip = isDefault ? translate.get("field_delete_disabled") : translate.get("delete");
                switch (__p.push("\n		\n<div class='control-group date'>\n	<div class='control-label'>\n		"), "company" == custom_form_type || "default_requester" == field_type || visible_in_portal || __p.push("\n			<span class='ficon-security private-symbol muted'></span>\n		"), __p.push("\n		"), "checkbox" != dom_type && __p.push("\n			<label class='custom-form-label'>", label, "</label>\n		"), __p.push("\n	</div>\n	<div class='options-wrapper'>\n		<div class='opt-inner-wrap'>\n			<div class = \"edit-field options ", edit_icon, ' tooltip" title="', translate.get("edit"), '" data-placement= "topRight"></div>\n			<div class = "delete-field options ', delete_icon, ' tooltip" title="', delete_tooltip, '" data-placement= "topRight"></div>\n		</div>\n	</div>\n	<div class=\'controls ', dom_type, "'>\n		<span class='overlay-field'></span>\n		"), dom_type) {
                    case "text":
                    case "phone_number":
                    case "url":
                    case "number":
                    case "decimal":
                    case "email":
                        __p.push("\n				<input type='text' disabled>\n				");
                        break;
                    case "requester":
                        __p.push("\n				<input type='text' disabled class='addcc'>\n				<span>&nbsp;<a href=\"#\" class=\"underline\">Add cc</a></span>\n				");
                        break;
                    case "date":
                        __p.push("\n				<input type='text' disabled>\n				<span class='ficon-date'></span>\n				");
                        break;
                    case "checkbox":
                        __p.push("\n				<input type='checkbox' disabled>\n				<span>", label, "</span>\n				");
                        break;
                    case "time_zone_dropdown":
                    case "dropdown":
                    case "dropdown_blank":
                        __p.push("\n				<select class='input-xlarge' disabled>\n					"), _.each(admin_choices, function(e) {
                            __p.push('\n						<option value="', e.value, '" > ', e.name, " </option>\n 					")
                        }), __p.push("\n				</select>\n				");
                        break;
                    case "paragraph":
                    case "html_paragraph":
                    case "description":
                        __p.push('\n				<textarea rows="5" disabled></textarea>\n				');
                        break;
                    case "nested_field":
                        __p.push(" \n				<select class='input-xlarge' disabled>\n					"), nestedTree = new NestedField(admin_choices);
                        var choice = nestedTree.getCategory();
                        choice = choice.split(","), __p.push("\n					"), _.each(choice, function(e) {
                            __p.push("\n						", e, "\n 					")
                        }), __p.push("\n					\n				</select>\n				");
                        break;
                    default:
                        __p.push("\n				<input type='text' disabled>\n				")
                }
                __p.push("\n	</div>\n</div>\n"), "has_section" in obj && has_section && __p.push('\n	<div class="section-wrapper" id="section-wrapper">\n		<a href="#" id="new-section" class="new-section">\n			<i class="rounded-add-icon fsize-12"></i>\n			', translate.get("new_section"), '\n		</a>\n		<ul id="section-container" class="section-container"></ul>\n	</div>\n'), __p.push("\n")
            }
            return __p.join("")
        }
    }.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["custom-form/template/formfield_props"] = function(obj) {
            var __p = [];
            with(obj || {}) {
                __p.push("");
                var isContactEmail = "default_email" == obj.field_type && "contact" == obj.custom_form_type,
                    isCompany = "company" == obj.custom_form_type,
                    jstDefaults = {
                        isDefault: /^default/.test(obj.field_type) ? !0 : !1,
                        isContactEmail: isContactEmail,
                        spanClass: isContactEmail || isCompany ? "span12" : "span6",
                        hideIfEmail: isContactEmail ? "hide" : ""
                    },
                    obj = _.extend({}, obj, jstDefaults);
                __p.push('\n<div class="custom-fields-props-dialog modal hide fade ', obj.isContactEmail || "company" == obj.custom_form_type ? "single-column" : "", "\" id='CustomPropsModal'>\n	<div class='modal-header clearfix'>\n		", JST["custom-form/template/custom_modal_header"](obj), '\n	</div>\n	<div class=\'modal-body custom-ticket-modal\'>\n		<form id="CustomProperties" action="#" class = "ticket-fields">\n			<input type="hidden" \n					name="custom-type" \n					value="', obj.field_type, '" />\n			<div class=\'sections-wrapper\'>\n				<div class="section">\n					<h3 class="title">', translate.get("behavior"), '</h3>\n					<div class="row-fluid"> \n						'), obj.isContactEmail || __p.push('\n							<div class="', obj.spanClass, '">\n							', JST["custom-form/template/agent_behavior"](obj), "\n							</div>\n						"), __p.push("\n						"), "default_requester" == obj.field_type && "default_internal_group" != obj.field_type && "default_internal_agent" != obj.field_type ? __p.push("\n							<div class='", obj.spanClass, "'>\n							", JST["custom-form/template/customer_behavior_requester"](obj), "\n							</div>\n						") : "company" != obj.custom_form_type && "default_internal_group" != obj.field_type && "default_internal_agent" != obj.field_type && __p.push("\n							<div class='", obj.spanClass, "'>\n							", JST["custom-form/template/customer_behavior"](obj), "\n							</div>\n						"), __p.push('\n					</div>\n				</div> \n				<div class="section ', "default_internal_agent" == obj.field_type || "default_internal_group" == obj.field_type ? "hide" : "", '">\n					'), "nested_field" == obj.field_type ? __p.push("\n						", JST["custom-form/template/nested_field_label"](obj), "\n				    ") : __p.push("\n				    	", JST["custom-form/template/custom_field_label"](obj), "\n					"), __p.push("\n				</div>\n\n				"), __p.push("\n\n				"), ("custom_dropdown" == obj.field_type || "default_status" == obj.field_type || "default_ticket_type" == obj.field_type) && __p.push("\n					", JST["custom-form/template/custom_dropdown"]({
                    obj: obj,
                    picklistIds: picklistIds,
                    shared_groups: sharedGroups,
                    shared_ownership_enabled: shared_ownership_enabled
                }), "\n				"), __p.push("\n				"), "nested_field" == obj.field_type && __p.push("\n					", JST["custom-form/template/nested_field_edit"](obj), "\n					", JST["custom-form/template/nested_field"](obj), "\n				"), __p.push('\n			</div>\n		</form>\n	</div>\n	<span class="seperator"></span> \n	<div class="alignright modal-footer">\n		'), ("custom_dropdown" == obj.field_type || "default_status" == obj.field_type || "default_ticket_type" == obj.field_type) && __p.push('\n			<a class="add-choice" id="addchoice" href="#"> \n				<span class=\'ficon-plus\'></span> \n				', translate.get("addNewChoice"), " \n			</a> \n		"), __p.push('\n		<a class="btn" id="cancel-btn">', translate.get("cancel"), '</a> \n		<input type="submit" value="', translate.get("done"), '" class="btn btn-primary" id=\'PropsSubmitBtn\' /> \n	</div>\n</div>\n')
            }
            return __p.join("")
        }
    }.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["custom-form/template/nested_field_edit"] = function(obj) {
            var __p = [];
            with(obj || {}) __p.push('<div id="nestedEdit" class="nested-edit-wrapper">\n	<h3 class=\'title\'> \n		', translate.get("dropdown_items_edit"), '\n	</h3>\n	<a href="#" id="nestedDoneEdit" class="button-link pull-right">', translate.get("preview"), '</a>\n    <div class="alert m0 mb5 mr5">\n    ', unescapeHtml(translate.get("nestedfield_helptext")), '\n    </div>\n  	<textarea id="nestedTextarea" rows="15" name="nestedTextarea" class="paragraph" rel="nestedfield"></textarea>\n</div>\n');
            return __p.join("")
        }
    }.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["custom-form/template/nested_field_label"] = function(obj) {
            var __p = [];
            with(obj || {}) __p.push('\r\n<h3 class="title">', translate.get("nestedFieldLabel"), ' </h3>\r\n	<div class="row-fluid">\r\n    <fieldset class="span4">\r\n    	<label class="title">', translate.get("level"), ' - 1</label>\r\n      <input type="text" \r\n      				placeholder="', translate.get("labelAgent"), ' *" \r\n      				rel="companion" \r\n      				data-companion="#customerslabel" \r\n      				name="custom-label" \r\n      				value="', label.replace(/"/g, "&quot;"), '"  \r\n      				id="agentlevel1label" \r\n      				data-level="1" />\r\n      <input type="text" placeholder="', translate.get("labelCustomer"), ' *" \r\n              id="customerslabel" name="custom-label-in-portal" \r\n              value="', label_in_portal.replace(/"/g, "&quot;"), '" />\r\n    </fieldset>\r\n\r\n    <fieldset class="span4">\r\n      <label class="title">', translate.get("level"), ' - 2</label>\r\n      <input type="text" \r\n      				placeholder="', translate.get("labelAgentLevel2"), ' *" \r\n      				data-companion="#customerslevel2label" \r\n      				id="agentlevel2label" \r\n      				rel="companion" \r\n      				name="agentlevel2label" \r\n      				value="', levels[0].label.replace(/"/g, "&quot;"), '" \r\n      				data-level="2" />\r\n\r\n    	<input type="text" \r\n              placeholder="', translate.get("labelCustomerLevel2"), ' *" \r\n              id="customerslevel2label" \r\n              name="customerslevel2label" \r\n              value="', levels[0].label_in_portal.replace(/"/g, "&quot;"), '"  />\r\n    </fieldset>\r\n\r\n    <fieldset class="span4">\r\n      <label class="title">', translate.get("level"), ' - 3</label>\r\n      <input type="text" \r\n      				placeholder="', translate.get("labelAgent"), '" \r\n      				data-companion="#customerslevel3label" \r\n      				rel="companion" \r\n      				id="agentlevel3label" \r\n      				name="agentlevel3label" \r\n      				value="', levels[1] && levels[1].label ? levels[1].label.replace(/"/g, "&quot;") : "", '"  \r\n      				data-level="3" />\r\n      <input type="text" \r\n              placeholder="', translate.get("labelCustomer"), '" \r\n              id="customerslevel3label" name="customerslevel3label" \r\n              value="', levels[1] && levels[1].label_in_portal ? levels[1].label_in_portal.replace(/"/g, "&quot;") : "", '"  />\r\n    </fieldset>\r\n</div>\n');
            return __p.join("")
        }
    }.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["custom-form/template/nested_field"] = function(obj) {
            var __p = [];
            with(obj || {}) __p.push('<div id="nestedFieldPreview" class="section hide">\n	<h3 class=\'title\'> \n		', translate.get("dropdownChoice"), " - ", translate.get("preview"), "\n	</h3>\n	<a href='#' class='button-link pull-right' id='nested-edit-button'>", translate.get("edit"), '</a>\n	<div class="custom-choices" id="nestedContainer">	    \n		<div id="nested-selectboxs">\n			<div class="alert m0 mb15">\n			', translate.get("nestedfield_helptext_preview"), '\n			</div>\n			<ul class="nested-selectboxs row-fluid">\n				<li class="span4">\n					<select name="nest-category" id="nest-category">\n						<option>...</option>\n					</select>\n				</li>\n				<li class="span4 subcategory">   \n					<span class="connector"></span>\n					<select name="nest-subcategory" id="nest-subcategory">\n						<option>...</option>\n					</select>\n				</li>\n				<li class="span4 item">               \n					<span class="connector"></span>\n					<select name="nest-item" id="nest-item" >\n						<option>...</option>\n					</select>\n				</li>            \n			</ul>\n		</div>\n	</div>\n</div> \n');
            return __p.join("")
        }
    }.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["custom-form/template/section_confirm"] = function(obj) {
            var __p = [],
                print = function() {
                    __p.push.apply(__p, arguments)
                };
            with(obj || {}) {
                __p.push("<!-- Modal -->\n<!-- available -->\n");
                var field_submit = "",
                    modal_id = "",
                    field_cancel = "";
                switch ("deleteNonSecField" == confirm_type ? (field_submit = "confirmDeleteSubmit", field_cancel = "confirmDeleteCancel", modal_id = "ConfirmModal") : (field_submit = "confirmFieldSubmit", field_cancel = "confirmFieldCancel", modal_id = "sectionConfirmModal"), __p.push('\n<div id="', modal_id, '" class="section-dialog-confirm modal hide fade" role="dialog" aria-hidden="true">\n  <div class="modal-header">\n    <!-- <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button> -->\n    <h3>\n      '), "available" != confirm_type ? __p.push("\n        ", translate.get("confirm_text"), "\n      ") : __p.push("\n        ", translate.get("oops_btn"), "\n      "), __p.push('\n    </h3>\n  </div>\n  <div class="modal-body">\n  	<input type="hidden" name="confirm_type" id="confirmType" value=\'', confirm_type, "'>\n    "), ("move" == confirm_type || "deleteSecField" == confirm_type) && __p.push("\n      <p> ", translate.get("would_you_like_to"), " </p>\n    "), __p.push("\n  	"), "move" == confirm_type || "deleteSecField" == confirm_type || "confirmDeleteField" == confirm_type ? (__p.push("\n  	"), "confirmDeleteField" != confirm_type && (__p.push('\n	    <label class="radio">\n		  <input type="radio" name="moveField" value="true" checked>\n\n			'), "move" == confirm_type ? __p.push("\n					", translate.get("move_keep_copy"), " \n			") : "deleteSecField" == confirm_type && __p.push("\n\n				", translate.get("delete_field_section"), "\n\n			"), __p.push("	\n		</label>\n		")), __p.push("\n		<label "), "confirmDeleteField" != confirm_type && __p.push(' class="radio" '), __p.push('>\n\n		  <input type="radio" name="moveField" value="false" '), "confirmDeleteField" == confirm_type && __p.push(' class="hide" checked '), __p.push(">\n\n		  "), "move" == confirm_type ? __p.push("\n\n				", translate.get("move_field_remove_section"), "\n\n			") : "deleteSecField" == confirm_type ? __p.push("\n\n				", translate.get("delete_permanent"), "  \n\n			") : "confirmDeleteField" == confirm_type && __p.push("	\n        \n        ", translate.get("delete_from_section"), "  \n\n      "), __p.push("\n		  \n		</label>\n\n	")) : "secToForm" == confirm_type ? __p.push("\n\n		<div>\n			", translate.get("field_remove_all_section"), " \n		</div>	\n\n	") : "deleteField" == confirm_type ? __p.push("\n\n		<div>\n			", translate.get("confirm_delete"), " 	\n		</div>\n\n	") : "deleteSection" == confirm_type ? __p.push('\n\n		<div>\n			<input type="hidden" name="confirm-section-id" id="confirm-section-id" value=\'', section_id, "'>\n			", translate.get("delete_section"), "\n		</div>\n\n	") : "deleteError" == confirm_type ? __p.push('\n\n		<div>\n			<input type="hidden" name="confirm-section-id" id="confirm-section-id" value=\'false\'>\n			', translate.get("section_has_fields"), " \n			 \n		</div>\n\n    ") : "deleteNonSecField" == confirm_type ? __p.push("\n\n    <div> \n      ", translate.get("confirmDelete"), "\n    </div>\n\n	") : __p.push("	\n\n		<div>\n			", translate.get("field_available"), " \n		</div>\n\n	"), __p.push('\n  </div>\n  <span class="seperator"></span> \n  <div class="alignright modal-footer">\n    '), "available" != confirm_type && __p.push('\n    <button class="btn" data-dismiss="modal" aria-hidden="true" id="', field_cancel, '">', translate.get("cancel"), " </button>\n    "), __p.push('\n    <a class="btn btn-primary" id="', field_submit, '">\n    	'), confirm_type) {
                    case "available":
                    case "deleteError":
                        print(translate.get("ok_btn"));
                        break;
                    case "move":
                        print(translate.get("confirm_btn"));
                        break;
                    case "deleteSecField":
                    case "deleteField":
                    case "confirmDeleteField":
                    case "deleteNonSecField":
                        print(translate.get("delete_btn"));
                        break;
                    case "deleteSection":
                        print(translate.get("delete_section_btn"));
                        break;
                    case "secToForm":
                        print(translate.get("continue_btn"))
                }
                __p.push("\n    </a> \n  </div>\n</div>\n")
            }
            return __p.join("")
        }
    }.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["custom-form/template/section_dialogue"] = function(obj) {
            var __p = [];
            with(obj || {}) __p.push("<div class=\"section-dialog custom-fields-props-dialog modal hide fade\" id='CustomPropsModal'>\n	<div class='modal-header clearfix'>\n		<h3 class=\"ellipsis modal-title pull-left\">\n			", translate.get("section_prop"), ' \n			<span class=\'field-label\'></span>\n		</h3>\n	</div>\n	<div class=\'modal-body\'>\n		<form id="sectionProperties" action="#" class = "form-horizontal sections-dialog-wrapper">\n			<div class="section">\n					<fieldset class="control-group">\n						<label class="control-label">\n							', translate.get("sectino_label"), '<span class="required_star">*</span>\n						</label>\n						<div class="controls">\n							<input type="hidden" name="section-id" class="hide" value="', obj.id, '" />\n							<input type="text" \n									placeholder="" \n									name="section-label" class="dialog-input-size required" value= "', obj.label.replace(/"/g, "&quot;"), '" />\n						</div>\n					</fieldset>\n					<fieldset class="control-group">\n						<label class="control-label">\n							', translate.get("section_type_is"), '<span class="required_star">*</span>\n						</label>\n						<div class="controls">\n							<select class="required dialog-input-size select2" id="section-type" name="section-type" multiple>\n								'), _.each(types, function(e) {
                __p.push('\n									<option value="', e.id, '" \n										');
                for (var t = 0; t < obj.picklist_ids.length; t++) e.id == obj.picklist_ids[t].picklist_value_id && __p.push("selected");
                __p.push(" >\n										", e.name, "\n									</option>\n								")
            }), __p.push('\n						    </select>\n						    <div class="muted"> ', translate.get("section_type_change"), '</div>\n					    </div>\n					</fieldset>					\n			</div>\n			<span class="seperator"></span> \n			<div class="alignright">\n				<a class="btn" id="sectionCancelBtn">', translate.get("cancel"), '</a> \n				<input type="submit" value="', translate.get("done"), '" class="btn btn-primary" id=\'sectionSubmitBtn\' /> \n			</div>\n		</form>\n	</div>\n</div>\n');
            return __p.join("")
        }
    }.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["custom-form/template/section_header"] = function(obj) {
            var __p = [];
            with(obj || {}) {
                __p.push("");
                for (var list_of_type = "", i = 0; i < obj.picklist_ids.length; i++)
                    for (var j = 0; j < types.length; j++) types[j].id == obj.picklist_ids[i].picklist_value_id && (list_of_type += types[j].value, i != obj.picklist_ids.length - 1 && (list_of_type += ", "));
                __p.push('\n<div class="section-title pull-left">\n	<div class="section-name">', obj.label, '</div>\n	<div class="section-type">\n		<span>Show when type is </span>\n		<span class="tooltip" title="', list_of_type, '">', list_of_type, '</span>\n	</div>\n	<div class = "section-icon ficon-edit section-edit pull-right"></div>\n</div>\n<div class = "section-icon pull-right ficon-trash-o section-delete" id="delete-section-icon"></div>\n')
            }
            return __p.join("")
        }
    }.call(this),
    function() {
        this.JST || (this.JST = {}), this.JST["custom-form/template/section"] = function(obj) {
            var __p = [];
            with(obj || {}) __p.push('<div class="section-header" id="section-header">\n	', JST["custom-form/template/section_header"]({
                obj: obj,
                types: types
            }), '\n</div>\n<div class="default-error-wrap">\n	<label class="default-error-text"> ', translate.get("default_field_error"), ' </label>\n</div>\n<ul class="section-body">\n	<div class="emptySectionInfo center">\n 		', translate.get("empty_section_info"), "\n	</div>\n</ul>\n");
            return __p.join("")
        }
    }.call(this);