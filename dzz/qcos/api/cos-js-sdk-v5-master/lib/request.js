var $ = (function () {
    var deletedIds = [];

    var slice = deletedIds.slice;

    var concat = deletedIds.concat;

    var push = deletedIds.push;

    var indexOf = deletedIds.indexOf;

    var class2type = {};

    var toString = class2type.toString;

    var hasOwn = class2type.hasOwnProperty;

    var support = {};


    var
        version = "1.11.1 -css,-css/addGetHookIf,-css/curCSS,-css/defaultDisplay,-css/hiddenVisibleSelectors,-css/support,-css/swap,-css/var/cssExpand,-css/var/isHidden,-css/var/rmargin,-css/var/rnumnonpx,-effects,-effects/Tween,-effects/animatedSelector,-effects/support,-dimensions,-offset,-deprecated,-event-alias,-wrap",

        // Define a local copy of jQuery
        jQuery = function (selector, context) {
            // The jQuery object is actually just the init constructor 'enhanced'
            // Need init if jQuery is called (just allow error to be thrown if not included)
            return new jQuery.fn.init(selector, context);
        },

        // Support: Android<4.1, IE<9
        // Make sure we trim BOM and NBSP
        rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,

        // Matches dashed string for camelizing
        rmsPrefix = /^-ms-/,
        rdashAlpha = /-([\da-z])/gi,

        // Used by jQuery.camelCase as callback to replace()
        fcamelCase = function (all, letter) {
            return letter.toUpperCase();
        };

    jQuery.fn = jQuery.prototype = {
        // The current version of jQuery being used
        jquery: version,

        constructor: jQuery,

        // Start with an empty selector
        selector: "",

        // The default length of a jQuery object is 0
        length: 0,

        toArray: function () {
            return slice.call(this);
        },

        // Get the Nth element in the matched element set OR
        // Get the whole matched element set as a clean array
        get: function (num) {
            return num != null ?

                // Return just the one element from the set
                ( num < 0 ? this[num + this.length] : this[num] ) :

                // Return all the elements in a clean array
                slice.call(this);
        },

        // Take an array of elements and push it onto the stack
        // (returning the new matched element set)
        pushStack: function (elems) {

            // Build a new jQuery matched element set
            var ret = jQuery.merge(this.constructor(), elems);

            // Add the old object onto the stack (as a reference)
            ret.prevObject = this;
            ret.context = this.context;

            // Return the newly-formed element set
            return ret;
        },

        // Execute a callback for every element in the matched set.
        // (You can seed the arguments with an array of args, but this is
        // only used internally.)
        each: function (callback, args) {
            return jQuery.each(this, callback, args);
        },

        map: function (callback) {
            return this.pushStack(jQuery.map(this, function (elem, i) {
                return callback.call(elem, i, elem);
            }));
        },

        slice: function () {
            return this.pushStack(slice.apply(this, arguments));
        },

        first: function () {
            return this.eq(0);
        },

        last: function () {
            return this.eq(-1);
        },

        eq: function (i) {
            var len = this.length,
                j = +i + ( i < 0 ? len : 0 );
            return this.pushStack(j >= 0 && j < len ? [this[j]] : []);
        },

        end: function () {
            return this.prevObject || this.constructor(null);
        },

        // For internal use only.
        // Behaves like an Array's method, not like a jQuery method.
        push: push,
        sort: deletedIds.sort,
        splice: deletedIds.splice
    };

    jQuery.extend = jQuery.fn.extend = function () {
        var src, copyIsArray, copy, name, options, clone,
            target = arguments[0] || {},
            i = 1,
            length = arguments.length,
            deep = false;

        // Handle a deep copy situation
        if (typeof target === "boolean") {
            deep = target;

            // skip the boolean and the target
            target = arguments[i] || {};
            i++;
        }

        // Handle case when target is a string or something (possible in deep copy)
        if (typeof target !== "object" && !jQuery.isFunction(target)) {
            target = {};
        }

        // extend jQuery itself if only one argument is passed
        if (i === length) {
            target = this;
            i--;
        }

        for (; i < length; i++) {
            // Only deal with non-null/undefined values
            if ((options = arguments[i]) != null) {
                // Extend the base object
                for (name in options) {
                    src = target[name];
                    copy = options[name];

                    // Prevent never-ending loop
                    if (target === copy) {
                        continue;
                    }

                    // Recurse if we're merging plain objects or arrays
                    if (deep && copy && ( jQuery.isPlainObject(copy) || (copyIsArray = jQuery.isArray(copy)) )) {
                        if (copyIsArray) {
                            copyIsArray = false;
                            clone = src && jQuery.isArray(src) ? src : [];

                        } else {
                            clone = src && jQuery.isPlainObject(src) ? src : {};
                        }

                        // Never move original objects, clone them
                        target[name] = jQuery.extend(deep, clone, copy);

                        // Don't bring in undefined values
                    } else if (copy !== undefined) {
                        target[name] = copy;
                    }
                }
            }
        }

        // Return the modified object
        return target;
    };

    jQuery.extend({
        // Unique for each copy of jQuery on the page
        expando: "jQuery" + ( version + Math.random() ).replace(/\D/g, ""),

        // Assume jQuery is ready without the ready module
        isReady: true,

        error: function (msg) {
            throw new Error(msg);
        },

        noop: function () {
        },

        // See test/unit/core.js for details concerning isFunction.
        // Since version 1.3, DOM methods and functions like alert
        // aren't supported. They return false on IE (#2968).
        isFunction: function (obj) {
            return jQuery.type(obj) === "function";
        },

        isArray: Array.isArray || function (obj) {
            return jQuery.type(obj) === "array";
        },

        isWindow: function (obj) {
            /* jshint eqeqeq: false */
            return obj != null && obj == obj.window;
        },

        isNumeric: function (obj) {
            // parseFloat NaNs numeric-cast false positives (null|true|false|"")
            // ...but misinterprets leading-number strings, particularly hex literals ("0x...")
            // subtraction forces infinities to NaN
            return !jQuery.isArray(obj) && obj - parseFloat(obj) >= 0;
        },

        isEmptyObject: function (obj) {
            var name;
            for (name in obj) {
                return false;
            }
            return true;
        },

        isPlainObject: function (obj) {
            var key;

            // Must be an Object.
            // Because of IE, we also have to check the presence of the constructor property.
            // Make sure that DOM nodes and window objects don't pass through, as well
            if (!obj || jQuery.type(obj) !== "object" || obj.nodeType || jQuery.isWindow(obj)) {
                return false;
            }

            try {
                // Not own constructor property must be Object
                if (obj.constructor &&
                    !hasOwn.call(obj, "constructor") &&
                    !hasOwn.call(obj.constructor.prototype, "isPrototypeOf")) {
                    return false;
                }
            } catch (e) {
                // IE8,9 Will throw exceptions on certain host objects #9897
                return false;
            }

            // Support: IE<9
            // Handle iteration over inherited properties before own properties.
            if (support.ownLast) {
                for (key in obj) {
                    return hasOwn.call(obj, key);
                }
            }

            // Own properties are enumerated firstly, so to speed up,
            // if last one is own, then all properties are own.
            for (key in obj) {
            }

            return key === undefined || hasOwn.call(obj, key);
        },

        type: function (obj) {
            if (obj == null) {
                return obj + "";
            }
            return typeof obj === "object" || typeof obj === "function" ?
                class2type[toString.call(obj)] || "object" :
                typeof obj;
        },

        // Evaluates a script in a global context
        // Workarounds based on findings by Jim Driscoll
        // http://weblogs.java.net/blog/driscoll/archive/2009/09/08/eval-javascript-global-context
        globalEval: function (data) {
            if (data && jQuery.trim(data)) {
                // We use execScript on Internet Explorer
                // We use an anonymous function so that context is window
                // rather than jQuery in Firefox
                ( window.execScript || function (data) {
                    window["eval"].call(window, data);
                } )(data);
            }
        },

        // Convert dashed to camelCase; used by the css and data modules
        // Microsoft forgot to hump their vendor prefix (#9572)
        camelCase: function (string) {
            return string.replace(rmsPrefix, "ms-").replace(rdashAlpha, fcamelCase);
        },

        nodeName: function (elem, name) {
            return elem.nodeName && elem.nodeName.toLowerCase() === name.toLowerCase();
        },

        // args is for internal usage only
        each: function (obj, callback, args) {
            var value,
                i = 0,
                length = obj.length,
                isArray = isArraylike(obj);

            if (args) {
                if (isArray) {
                    for (; i < length; i++) {
                        value = callback.apply(obj[i], args);

                        if (value === false) {
                            break;
                        }
                    }
                } else {
                    for (i in obj) {
                        value = callback.apply(obj[i], args);

                        if (value === false) {
                            break;
                        }
                    }
                }

                // A special, fast, case for the most common use of each
            } else {
                if (isArray) {
                    for (; i < length; i++) {
                        value = callback.call(obj[i], i, obj[i]);

                        if (value === false) {
                            break;
                        }
                    }
                } else {
                    for (i in obj) {
                        value = callback.call(obj[i], i, obj[i]);

                        if (value === false) {
                            break;
                        }
                    }
                }
            }

            return obj;
        },

        // Support: Android<4.1, IE<9
        trim: function (text) {
            return text == null ?
                "" :
                ( text + "" ).replace(rtrim, "");
        },

        // results is for internal usage only
        makeArray: function (arr, results) {
            var ret = results || [];

            if (arr != null) {
                if (isArraylike(Object(arr))) {
                    jQuery.merge(ret,
                        typeof arr === "string" ?
                            [arr] : arr
                    );
                } else {
                    push.call(ret, arr);
                }
            }

            return ret;
        },

        inArray: function (elem, arr, i) {
            var len;

            if (arr) {
                if (indexOf) {
                    return indexOf.call(arr, elem, i);
                }

                len = arr.length;
                i = i ? i < 0 ? Math.max(0, len + i) : i : 0;

                for (; i < len; i++) {
                    // Skip accessing in sparse arrays
                    if (i in arr && arr[i] === elem) {
                        return i;
                    }
                }
            }

            return -1;
        },

        merge: function (first, second) {
            var len = +second.length,
                j = 0,
                i = first.length;

            while (j < len) {
                first[i++] = second[j++];
            }

            // Support: IE<9
            // Workaround casting of .length to NaN on otherwise arraylike objects (e.g., NodeLists)
            if (len !== len) {
                while (second[j] !== undefined) {
                    first[i++] = second[j++];
                }
            }

            first.length = i;

            return first;
        },

        grep: function (elems, callback, invert) {
            var callbackInverse,
                matches = [],
                i = 0,
                length = elems.length,
                callbackExpect = !invert;

            // Go through the array, only saving the items
            // that pass the validator function
            for (; i < length; i++) {
                callbackInverse = !callback(elems[i], i);
                if (callbackInverse !== callbackExpect) {
                    matches.push(elems[i]);
                }
            }

            return matches;
        },

        // arg is for internal usage only
        map: function (elems, callback, arg) {
            var value,
                i = 0,
                length = elems.length,
                isArray = isArraylike(elems),
                ret = [];

            // Go through the array, translating each of the items to their new values
            if (isArray) {
                for (; i < length; i++) {
                    value = callback(elems[i], i, arg);

                    if (value != null) {
                        ret.push(value);
                    }
                }

                // Go through every key on the object,
            } else {
                for (i in elems) {
                    value = callback(elems[i], i, arg);

                    if (value != null) {
                        ret.push(value);
                    }
                }
            }

            // Flatten any nested arrays
            return concat.apply([], ret);
        },

        // A global GUID counter for objects
        guid: 1,

        // Bind a function to a context, optionally partially applying any
        // arguments.
        proxy: function (fn, context) {
            var args, proxy, tmp;

            if (typeof context === "string") {
                tmp = fn[context];
                context = fn;
                fn = tmp;
            }

            // Quick check to determine if target is callable, in the spec
            // this throws a TypeError, but we will just return undefined.
            if (!jQuery.isFunction(fn)) {
                return undefined;
            }

            // Simulated bind
            args = slice.call(arguments, 2);
            proxy = function () {
                return fn.apply(context || this, args.concat(slice.call(arguments)));
            };

            // Set the guid of unique handler to the same of original handler, so it can be removed
            proxy.guid = fn.guid = fn.guid || jQuery.guid++;

            return proxy;
        },

        now: function () {
            return +( new Date() );
        },

        // jQuery.support is not used in Core but other projects attach their
        // properties to it so it needs to exist.
        support: support
    });

// Populate the class2type map
    jQuery.each("Boolean Number String Function Array Date RegExp Object Error".split(" "), function (i, name) {
        class2type["[object " + name + "]"] = name.toLowerCase();
    });

    function isArraylike(obj) {
        var length = obj.length,
            type = jQuery.type(obj);

        if (type === "function" || jQuery.isWindow(obj)) {
            return false;
        }

        if (obj.nodeType === 1 && length) {
            return true;
        }

        return type === "array" || length === 0 ||
            typeof length === "number" && length > 0 && ( length - 1 ) in obj;
    }


// Initialize a jQuery object


// A central reference to the root jQuery(document)
    var rootjQuery,

        // Use the correct document accordingly with window argument (sandbox)
        document = window.document,

        // A simple way to check for HTML strings
        // Prioritize #id over <tag> to avoid XSS via location.hash (#9521)
        // Strict HTML recognition (#11290: must start with <)
        rquickExpr = /^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]*))$/,

        init = jQuery.fn.init = function (selector, context) {
            var match, elem;

            // HANDLE: $(""), $(null), $(undefined), $(false)
            if (!selector) {
                return this;
            }

            // Handle HTML strings
            if (typeof selector === "string") {
                if (selector.charAt(0) === "<" && selector.charAt(selector.length - 1) === ">" && selector.length >= 3) {
                    // Assume that strings that start and end with <> are HTML and skip the regex check
                    match = [null, selector, null];

                } else {
                    match = rquickExpr.exec(selector);
                }

                // Match html or make sure no context is specified for #id
                if (match && (match[1] || !context)) {

                    // HANDLE: $(html) -> $(array)
                    if (match[1]) {
                        context = context instanceof jQuery ? context[0] : context;

                        // scripts is true for back-compat
                        // Intentionally let the error be thrown if parseHTML is not present
                        jQuery.merge(this, jQuery.parseHTML(
                            match[1],
                            context && context.nodeType ? context.ownerDocument || context : document,
                            true
                        ));

                        // HANDLE: $(html, props)
                        if (rsingleTag.test(match[1]) && jQuery.isPlainObject(context)) {
                            for (match in context) {
                                // Properties of context are called as methods if possible
                                if (jQuery.isFunction(this[match])) {
                                    this[match](context[match]);

                                    // ...and otherwise set as attributes
                                } else {
                                    this.attr(match, context[match]);
                                }
                            }
                        }

                        return this;

                        // HANDLE: $(#id)
                    } else {
                        elem = document.getElementById(match[2]);

                        // Check parentNode to catch when Blackberry 4.6 returns
                        // nodes that are no longer in the document #6963
                        if (elem && elem.parentNode) {
                            // Handle the case where IE and Opera return items
                            // by name instead of ID
                            if (elem.id !== match[2]) {
                                return rootjQuery.find(selector);
                            }

                            // Otherwise, we inject the element directly into the jQuery object
                            this.length = 1;
                            this[0] = elem;
                        }

                        this.context = document;
                        this.selector = selector;
                        return this;
                    }

                    // HANDLE: $(expr, $(...))
                } else if (!context || context.jquery) {
                    return ( context || rootjQuery ).find(selector);

                    // HANDLE: $(expr, context)
                    // (which is just equivalent to: $(context).find(expr)
                } else {
                    return this.constructor(context).find(selector);
                }

                // HANDLE: $(DOMElement)
            } else if (selector.nodeType) {
                this.context = this[0] = selector;
                this.length = 1;
                return this;

                // HANDLE: $(function)
                // Shortcut for document ready
            } else if (jQuery.isFunction(selector)) {
                return typeof rootjQuery.ready !== "undefined" ?
                    rootjQuery.ready(selector) :
                    // Execute immediately if ready is not present
                    selector(jQuery);
            }

            if (selector.selector !== undefined) {
                this.selector = selector.selector;
                this.context = selector.context;
            }

            return jQuery.makeArray(selector, this);
        };

// Give the init function the jQuery prototype for later instantiation
    init.prototype = jQuery.fn;

// Initialize central reference
    rootjQuery = jQuery(document);


    var rnotwhite = (/\S+/g);

// String to Object options format cache
    var optionsCache = {};

// Convert String-formatted options into Object-formatted ones and store in cache
    function createOptions(options) {
        var object = optionsCache[options] = {};
        jQuery.each(options.match(rnotwhite) || [], function (_, flag) {
            object[flag] = true;
        });
        return object;
    }

    /*
     * Create a callback list using the following parameters:
     *
     *	options: an optional list of space-separated options that will change how
     *			the callback list behaves or a more traditional option object
     *
     * By default a callback list will act like an event callback list and can be
     * "fired" multiple times.
     *
     * Possible options:
     *
     *	once:			will ensure the callback list can only be fired once (like a Deferred)
     *
     *	memory:			will keep track of previous values and will call any callback added
     *					after the list has been fired right away with the latest "memorized"
     *					values (like a Deferred)
     *
     *	unique:			will ensure a callback can only be added once (no duplicate in the list)
     *
     *	stopOnFalse:	interrupt callings when a callback returns false
     *
     */
    jQuery.Callbacks = function (options) {

        // Convert options from String-formatted to Object-formatted if needed
        // (we check in cache first)
        options = typeof options === "string" ?
            ( optionsCache[options] || createOptions(options) ) :
            jQuery.extend({}, options);

        var // Flag to know if list is currently firing
            firing,
            // Last fire value (for non-forgettable lists)
            memory,
            // Flag to know if list was already fired
            fired,
            // End of the loop when firing
            firingLength,
            // Index of currently firing callback (modified by remove if needed)
            firingIndex,
            // First callback to fire (used internally by add and fireWith)
            firingStart,
            // Actual callback list
            list = [],
            // Stack of fire calls for repeatable lists
            stack = !options.once && [],
            // Fire callbacks
            fire = function (data) {
                memory = options.memory && data;
                fired = true;
                firingIndex = firingStart || 0;
                firingStart = 0;
                firingLength = list.length;
                firing = true;
                for (; list && firingIndex < firingLength; firingIndex++) {
                    if (list[firingIndex].apply(data[0], data[1]) === false && options.stopOnFalse) {
                        memory = false; // To prevent further calls using add
                        break;
                    }
                }
                firing = false;
                if (list) {
                    if (stack) {
                        if (stack.length) {
                            fire(stack.shift());
                        }
                    } else if (memory) {
                        list = [];
                    } else {
                        self.disable();
                    }
                }
            },
            // Actual Callbacks object
            self = {
                // Add a callback or a collection of callbacks to the list
                add: function () {
                    if (list) {
                        // First, we save the current length
                        var start = list.length;
                        (function add(args) {
                            jQuery.each(args, function (_, arg) {
                                var type = jQuery.type(arg);
                                if (type === "function") {
                                    if (!options.unique || !self.has(arg)) {
                                        list.push(arg);
                                    }
                                } else if (arg && arg.length && type !== "string") {
                                    // Inspect recursively
                                    add(arg);
                                }
                            });
                        })(arguments);
                        // Do we need to add the callbacks to the
                        // current firing batch?
                        if (firing) {
                            firingLength = list.length;
                            // With memory, if we're not firing then
                            // we should call right away
                        } else if (memory) {
                            firingStart = start;
                            fire(memory);
                        }
                    }
                    return this;
                },
                // Remove a callback from the list
                remove: function () {
                    if (list) {
                        jQuery.each(arguments, function (_, arg) {
                            var index;
                            while (( index = jQuery.inArray(arg, list, index) ) > -1) {
                                list.splice(index, 1);
                                // Handle firing indexes
                                if (firing) {
                                    if (index <= firingLength) {
                                        firingLength--;
                                    }
                                    if (index <= firingIndex) {
                                        firingIndex--;
                                    }
                                }
                            }
                        });
                    }
                    return this;
                },
                // Check if a given callback is in the list.
                // If no argument is given, return whether or not list has callbacks attached.
                has: function (fn) {
                    return fn ? jQuery.inArray(fn, list) > -1 : !!( list && list.length );
                },
                // Remove all callbacks from the list
                empty: function () {
                    list = [];
                    firingLength = 0;
                    return this;
                },
                // Have the list do nothing anymore
                disable: function () {
                    list = stack = memory = undefined;
                    return this;
                },
                // Is it disabled?
                disabled: function () {
                    return !list;
                },
                // Lock the list in its current state
                lock: function () {
                    stack = undefined;
                    if (!memory) {
                        self.disable();
                    }
                    return this;
                },
                // Is it locked?
                locked: function () {
                    return !stack;
                },
                // Call all callbacks with the given context and arguments
                fireWith: function (context, args) {
                    if (list && ( !fired || stack )) {
                        args = args || [];
                        args = [context, args.slice ? args.slice() : args];
                        if (firing) {
                            stack.push(args);
                        } else {
                            fire(args);
                        }
                    }
                    return this;
                },
                // Call all the callbacks with the given arguments
                fire: function () {
                    self.fireWith(this, arguments);
                    return this;
                },
                // To know if the callbacks have already been called at least once
                fired: function () {
                    return !!fired;
                }
            };

        return self;
    };


    jQuery.extend({

        Deferred: function (func) {
            var tuples = [
                    // action, add listener, listener list, final state
                    ["resolve", "done", jQuery.Callbacks("once memory"), "resolved"],
                    ["reject", "fail", jQuery.Callbacks("once memory"), "rejected"],
                    ["notify", "progress", jQuery.Callbacks("memory")]
                ],
                state = "pending",
                promise = {
                    state: function () {
                        return state;
                    },
                    always: function () {
                        deferred.done(arguments).fail(arguments);
                        return this;
                    },
                    then: function (/* fnDone, fnFail, fnProgress */) {
                        var fns = arguments;
                        return jQuery.Deferred(function (newDefer) {
                            jQuery.each(tuples, function (i, tuple) {
                                var fn = jQuery.isFunction(fns[i]) && fns[i];
                                // deferred[ done | fail | progress ] for forwarding actions to newDefer
                                deferred[tuple[1]](function () {
                                    var returned = fn && fn.apply(this, arguments);
                                    if (returned && jQuery.isFunction(returned.promise)) {
                                        returned.promise()
                                            .done(newDefer.resolve)
                                            .fail(newDefer.reject)
                                            .progress(newDefer.notify);
                                    } else {
                                        newDefer[tuple[0] + "With"](this === promise ? newDefer.promise() : this, fn ? [returned] : arguments);
                                    }
                                });
                            });
                            fns = null;
                        }).promise();
                    },
                    // Get a promise for this deferred
                    // If obj is provided, the promise aspect is added to the object
                    promise: function (obj) {
                        return obj != null ? jQuery.extend(obj, promise) : promise;
                    }
                },
                deferred = {};

            // Keep pipe for back-compat
            promise.pipe = promise.then;

            // Add list-specific methods
            jQuery.each(tuples, function (i, tuple) {
                var list = tuple[2],
                    stateString = tuple[3];

                // promise[ done | fail | progress ] = list.add
                promise[tuple[1]] = list.add;

                // Handle state
                if (stateString) {
                    list.add(function () {
                        // state = [ resolved | rejected ]
                        state = stateString;

                        // [ reject_list | resolve_list ].disable; progress_list.lock
                    }, tuples[i ^ 1][2].disable, tuples[2][2].lock);
                }

                // deferred[ resolve | reject | notify ]
                deferred[tuple[0]] = function () {
                    deferred[tuple[0] + "With"](this === deferred ? promise : this, arguments);
                    return this;
                };
                deferred[tuple[0] + "With"] = list.fireWith;
            });

            // Make the deferred a promise
            promise.promise(deferred);

            // Call given func if any
            if (func) {
                func.call(deferred, deferred);
            }

            // All done!
            return deferred;
        },

        // Deferred helper
        when: function (subordinate /* , ..., subordinateN */) {
            var i = 0,
                resolveValues = slice.call(arguments),
                length = resolveValues.length,

                // the count of uncompleted subordinates
                remaining = length !== 1 || ( subordinate && jQuery.isFunction(subordinate.promise) ) ? length : 0,

                // the master Deferred. If resolveValues consist of only a single Deferred, just use that.
                deferred = remaining === 1 ? subordinate : jQuery.Deferred(),

                // Update function for both resolve and progress values
                updateFunc = function (i, contexts, values) {
                    return function (value) {
                        contexts[i] = this;
                        values[i] = arguments.length > 1 ? slice.call(arguments) : value;
                        if (values === progressValues) {
                            deferred.notifyWith(contexts, values);

                        } else if (!(--remaining)) {
                            deferred.resolveWith(contexts, values);
                        }
                    };
                },

                progressValues, progressContexts, resolveContexts;

            // add listeners to Deferred subordinates; treat others as resolved
            if (length > 1) {
                progressValues = new Array(length);
                progressContexts = new Array(length);
                resolveContexts = new Array(length);
                for (; i < length; i++) {
                    if (resolveValues[i] && jQuery.isFunction(resolveValues[i].promise)) {
                        resolveValues[i].promise()
                            .done(updateFunc(i, resolveContexts, resolveValues))
                            .fail(deferred.reject)
                            .progress(updateFunc(i, progressContexts, progressValues));
                    } else {
                        --remaining;
                    }
                }
            }

            // if we're not waiting on anything, resolve the master
            if (!remaining) {
                deferred.resolveWith(resolveContexts, resolveValues);
            }

            return deferred.promise();
        }
    });


// The deferred used on DOM ready
    var readyList;

    jQuery.fn.ready = function (fn) {
        // Add the callback
        jQuery.ready.promise().done(fn);

        return this;
    };

    jQuery.extend({
        // Is the DOM ready to be used? Set to true once it occurs.
        isReady: false,

        // A counter to track how many items to wait for before
        // the ready event fires. See #6781
        readyWait: 1,

        // Hold (or release) the ready event
        holdReady: function (hold) {
            if (hold) {
                jQuery.readyWait++;
            } else {
                jQuery.ready(true);
            }
        },

        // Handle when the DOM is ready
        ready: function (wait) {

            // Abort if there are pending holds or we're already ready
            if (wait === true ? --jQuery.readyWait : jQuery.isReady) {
                return;
            }

            // Make sure body exists, at least, in case IE gets a little overzealous (ticket #5443).
            if (!document.body) {
                return setTimeout(jQuery.ready);
            }

            // Remember that the DOM is ready
            jQuery.isReady = true;

            // If a normal DOM Ready event fired, decrement, and wait if need be
            if (wait !== true && --jQuery.readyWait > 0) {
                return;
            }

            // If there are functions bound, to execute
            readyList.resolveWith(document, [jQuery]);

            // Trigger any bound ready events
            if (jQuery.fn.triggerHandler) {
                jQuery(document).triggerHandler("ready");
                jQuery(document).off("ready");
            }
        }
    });

    /**
     * Clean-up method for dom ready events
     */
    function detach() {
        if (document.addEventListener) {
            document.removeEventListener("DOMContentLoaded", completed, false);
            window.removeEventListener("load", completed, false);

        } else {
            document.detachEvent("onreadystatechange", completed);
            window.detachEvent("onload", completed);
        }
    }

    /**
     * The ready event handler and self cleanup method
     */
    function completed() {
        // readyState === "complete" is good enough for us to call the dom ready in oldIE
        if (document.addEventListener || event.type === "load" || document.readyState === "complete") {
            detach();
            jQuery.ready();
        }
    }

    jQuery.ready.promise = function (obj) {
        if (!readyList) {

            readyList = jQuery.Deferred();

            // Catch cases where $(document).ready() is called after the browser event has already occurred.
            // we once tried to use readyState "interactive" here, but it caused issues like the one
            // discovered by ChrisS here: http://bugs.jquery.com/ticket/12282#comment:15
            if (document.readyState === "complete") {
                // Handle it asynchronously to allow scripts the opportunity to delay ready
                setTimeout(jQuery.ready);

                // Standards-based browsers support DOMContentLoaded
            } else if (document.addEventListener) {
                // Use the handy event callback
                document.addEventListener("DOMContentLoaded", completed, false);

                // A fallback to window.onload, that will always work
                window.addEventListener("load", completed, false);

                // If IE event model is used
            } else {
                // Ensure firing before onload, maybe late but safe also for iframes
                document.attachEvent("onreadystatechange", completed);

                // A fallback to window.onload, that will always work
                window.attachEvent("onload", completed);

                // If IE and not a frame
                // continually check to see if the document is ready
                var top = false;

                try {
                    top = window.frameElement == null && document.documentElement;
                } catch (e) {
                }

                if (top && top.doScroll) {
                    (function doScrollCheck() {
                        if (!jQuery.isReady) {

                            try {
                                // Use the trick by Diego Perini
                                // http://javascript.nwbox.com/IEContentLoaded/
                                top.doScroll("left");
                            } catch (e) {
                                return setTimeout(doScrollCheck, 50);
                            }

                            // detach all dom ready events
                            detach();

                            // and execute any waiting functions
                            jQuery.ready();
                        }
                    })();
                }
            }
        }
        return readyList.promise(obj);
    };


    var strundefined = typeof undefined;


// Support: IE<9
// Iteration over object's inherited properties before its own
    var i;
    for (i in jQuery(support)) {
        break;
    }
    support.ownLast = i !== "0";

// Note: most support tests are defined in their respective modules.
// false until the test is run
    support.inlineBlockNeedsLayout = false;


// Execute ASAP in case we need to set body.style.zoom
    jQuery(function () {
        // Minified: var a,b,c,d
        var val, div, body, container;

        body = document.getElementsByTagName("body")[0];
        if (!body || !body.style) {
            // Return for frameset docs that don't have a body
            return;
        }

        // Setup
        div = document.createElement("div");
        container = document.createElement("div");
        container.style.cssText = "position:absolute;border:0;width:0;height:0;top:0;left:-9999px";
        body.appendChild(container).appendChild(div);

        if (typeof div.style.zoom !== strundefined) {
            // Support: IE<8
            // Check if natively block-level elements act like inline-block
            // elements when setting their display to 'inline' and giving
            // them layout
            div.style.cssText = "display:inline;margin:0;border:0;padding:1px;width:1px;zoom:1";

            support.inlineBlockNeedsLayout = val = div.offsetWidth === 3;
            if (val) {
                // Prevent IE 6 from affecting layout for positioned elements #11048
                // Prevent IE from shrinking the body in IE 7 mode #12869
                // Support: IE<8
                body.style.zoom = 1;
            }
        }

        body.removeChild(container);
    });


    (function () {
        var div = document.createElement("div");

        // Execute the test only if not already executed in another module.
        if (support.deleteExpando == null) {
            // Support: IE<9
            support.deleteExpando = true;
            try {
                delete div.test;
            } catch (e) {
                support.deleteExpando = false;
            }
        }

        // Null elements to avoid leaks in IE.
        div = null;
    })();


    /**
     * Determines whether an object can have data
     */
    jQuery.acceptData = function (elem) {
        var noData = jQuery.noData[(elem.nodeName + " ").toLowerCase()],
            nodeType = +elem.nodeType || 1;

        // Do not set data on non-element DOM nodes because it will not be cleared (#8335).
        return nodeType !== 1 && nodeType !== 9 ?
            false :

            // Nodes accept data unless otherwise specified; rejection can be conditional
            !noData || noData !== true && elem.getAttribute("classid") === noData;
    };


    var rbrace = /^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,
        rmultiDash = /([A-Z])/g;

    function dataAttr(elem, key, data) {
        // If nothing was found internally, try to fetch any
        // data from the HTML5 data-* attribute
        if (data === undefined && elem.nodeType === 1) {

            var name = "data-" + key.replace(rmultiDash, "-$1").toLowerCase();

            data = elem.getAttribute(name);

            if (typeof data === "string") {
                try {
                    data = data === "true" ? true :
                        data === "false" ? false :
                            data === "null" ? null :
                                // Only convert to a number if it doesn't change the string
                                +data + "" === data ? +data :
                                    rbrace.test(data) ? jQuery.parseJSON(data) :
                                        data;
                } catch (e) {
                }

                // Make sure we set the data so it isn't changed later
                jQuery.data(elem, key, data);

            } else {
                data = undefined;
            }
        }

        return data;
    }

// checks a cache object for emptiness
    function isEmptyDataObject(obj) {
        var name;
        for (name in obj) {

            // if the public data object is empty, the private is still empty
            if (name === "data" && jQuery.isEmptyObject(obj[name])) {
                continue;
            }
            if (name !== "toJSON") {
                return false;
            }
        }

        return true;
    }

    function internalData(elem, name, data, pvt /* Internal Use Only */) {
        if (!jQuery.acceptData(elem)) {
            return;
        }

        var ret, thisCache,
            internalKey = jQuery.expando,

            // We have to handle DOM nodes and JS objects differently because IE6-7
            // can't GC object references properly across the DOM-JS boundary
            isNode = elem.nodeType,

            // Only DOM nodes need the global jQuery cache; JS object data is
            // attached directly to the object so GC can occur automatically
            cache = isNode ? jQuery.cache : elem,

            // Only defining an ID for JS objects if its cache already exists allows
            // the code to shortcut on the same path as a DOM node with no cache
            id = isNode ? elem[internalKey] : elem[internalKey] && internalKey;

        // Avoid doing any more work than we need to when trying to get data on an
        // object that has no data at all
        if ((!id || !cache[id] || (!pvt && !cache[id].data)) && data === undefined && typeof name === "string") {
            return;
        }

        if (!id) {
            // Only DOM nodes need a new unique ID for each element since their data
            // ends up in the global cache
            if (isNode) {
                id = elem[internalKey] = deletedIds.pop() || jQuery.guid++;
            } else {
                id = internalKey;
            }
        }

        if (!cache[id]) {
            // Avoid exposing jQuery metadata on plain JS objects when the object
            // is serialized using JSON.stringify
            cache[id] = isNode ? {} : {toJSON: jQuery.noop};
        }

        // An object can be passed to jQuery.data instead of a key/value pair; this gets
        // shallow copied over onto the existing cache
        if (typeof name === "object" || typeof name === "function") {
            if (pvt) {
                cache[id] = jQuery.extend(cache[id], name);
            } else {
                cache[id].data = jQuery.extend(cache[id].data, name);
            }
        }

        thisCache = cache[id];

        // jQuery data() is stored in a separate object inside the object's internal data
        // cache in order to avoid key collisions between internal data and user-defined
        // data.
        if (!pvt) {
            if (!thisCache.data) {
                thisCache.data = {};
            }

            thisCache = thisCache.data;
        }

        if (data !== undefined) {
            thisCache[jQuery.camelCase(name)] = data;
        }

        // Check for both converted-to-camel and non-converted data property names
        // If a data property was specified
        if (typeof name === "string") {

            // First Try to find as-is property data
            ret = thisCache[name];

            // Test for null|undefined property data
            if (ret == null) {

                // Try to find the camelCased property
                ret = thisCache[jQuery.camelCase(name)];
            }
        } else {
            ret = thisCache;
        }

        return ret;
    }

    function internalRemoveData(elem, name, pvt) {
        if (!jQuery.acceptData(elem)) {
            return;
        }

        var thisCache, i,
            isNode = elem.nodeType,

            // See jQuery.data for more information
            cache = isNode ? jQuery.cache : elem,
            id = isNode ? elem[jQuery.expando] : jQuery.expando;

        // If there is already no cache entry for this object, there is no
        // purpose in continuing
        if (!cache[id]) {
            return;
        }

        if (name) {

            thisCache = pvt ? cache[id] : cache[id].data;

            if (thisCache) {

                // Support array or space separated string names for data keys
                if (!jQuery.isArray(name)) {

                    // try the string as a key before any manipulation
                    if (name in thisCache) {
                        name = [name];
                    } else {

                        // split the camel cased version by spaces unless a key with the spaces exists
                        name = jQuery.camelCase(name);
                        if (name in thisCache) {
                            name = [name];
                        } else {
                            name = name.split(" ");
                        }
                    }
                } else {
                    // If "name" is an array of keys...
                    // When data is initially created, via ("key", "val") signature,
                    // keys will be converted to camelCase.
                    // Since there is no way to tell _how_ a key was added, remove
                    // both plain key and camelCase key. #12786
                    // This will only penalize the array argument path.
                    name = name.concat(jQuery.map(name, jQuery.camelCase));
                }

                i = name.length;
                while (i--) {
                    delete thisCache[name[i]];
                }

                // If there is no data left in the cache, we want to continue
                // and let the cache object itself get destroyed
                if (pvt ? !isEmptyDataObject(thisCache) : !jQuery.isEmptyObject(thisCache)) {
                    return;
                }
            }
        }

        // See jQuery.data for more information
        if (!pvt) {
            delete cache[id].data;

            // Don't destroy the parent cache unless the internal data object
            // had been the only thing left in it
            if (!isEmptyDataObject(cache[id])) {
                return;
            }
        }

        // Destroy the cache
        if (isNode) {
            jQuery.cleanData([elem], true);

            // Use delete when supported for expandos or `cache` is not a window per isWindow (#10080)
            /* jshint eqeqeq: false */
        } else if (support.deleteExpando || cache != cache.window) {
            /* jshint eqeqeq: true */
            delete cache[id];

            // When all else fails, null
        } else {
            cache[id] = null;
        }
    }

    jQuery.extend({
        cache: {},

        // The following elements (space-suffixed to avoid Object.prototype collisions)
        // throw uncatchable exceptions if you attempt to set expando properties
        noData: {
            "applet ": true,
            "embed ": true,
            // ...but Flash objects (which have this classid) *can* handle expandos
            "object ": "clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
        },

        hasData: function (elem) {
            elem = elem.nodeType ? jQuery.cache[elem[jQuery.expando]] : elem[jQuery.expando];
            return !!elem && !isEmptyDataObject(elem);
        },

        data: function (elem, name, data) {
            return internalData(elem, name, data);
        },

        removeData: function (elem, name) {
            return internalRemoveData(elem, name);
        },

        // For internal use only.
        _data: function (elem, name, data) {
            return internalData(elem, name, data, true);
        },

        _removeData: function (elem, name) {
            return internalRemoveData(elem, name, true);
        }
    });

    jQuery.fn.extend({
        data: function (key, value) {
            var i, name, data,
                elem = this[0],
                attrs = elem && elem.attributes;

            // Special expections of .data basically thwart jQuery.access,
            // so implement the relevant behavior ourselves

            // Gets all values
            if (key === undefined) {
                if (this.length) {
                    data = jQuery.data(elem);

                    if (elem.nodeType === 1 && !jQuery._data(elem, "parsedAttrs")) {
                        i = attrs.length;
                        while (i--) {

                            // Support: IE11+
                            // The attrs elements can be null (#14894)
                            if (attrs[i]) {
                                name = attrs[i].name;
                                if (name.indexOf("data-") === 0) {
                                    name = jQuery.camelCase(name.slice(5));
                                    dataAttr(elem, name, data[name]);
                                }
                            }
                        }
                        jQuery._data(elem, "parsedAttrs", true);
                    }
                }

                return data;
            }

            // Sets multiple values
            if (typeof key === "object") {
                return this.each(function () {
                    jQuery.data(this, key);
                });
            }

            return arguments.length > 1 ?

                // Sets one value
                this.each(function () {
                    jQuery.data(this, key, value);
                }) :

                // Gets one value
                // Try to fetch any internally stored data first
                elem ? dataAttr(elem, key, jQuery.data(elem, key)) : undefined;
        },

        removeData: function (key) {
            return this.each(function () {
                jQuery.removeData(this, key);
            });
        }
    });


    jQuery.extend({
        queue: function (elem, type, data) {
            var queue;

            if (elem) {
                type = ( type || "fx" ) + "queue";
                queue = jQuery._data(elem, type);

                // Speed up dequeue by getting out quickly if this is just a lookup
                if (data) {
                    if (!queue || jQuery.isArray(data)) {
                        queue = jQuery._data(elem, type, jQuery.makeArray(data));
                    } else {
                        queue.push(data);
                    }
                }
                return queue || [];
            }
        },

        dequeue: function (elem, type) {
            type = type || "fx";

            var queue = jQuery.queue(elem, type),
                startLength = queue.length,
                fn = queue.shift(),
                hooks = jQuery._queueHooks(elem, type),
                next = function () {
                    jQuery.dequeue(elem, type);
                };

            // If the fx queue is dequeued, always remove the progress sentinel
            if (fn === "inprogress") {
                fn = queue.shift();
                startLength--;
            }

            if (fn) {

                // Add a progress sentinel to prevent the fx queue from being
                // automatically dequeued
                if (type === "fx") {
                    queue.unshift("inprogress");
                }

                // clear up the last queue stop function
                delete hooks.stop;
                fn.call(elem, next, hooks);
            }

            if (!startLength && hooks) {
                hooks.empty.fire();
            }
        },

        // not intended for public consumption - generates a queueHooks object, or returns the current one
        _queueHooks: function (elem, type) {
            var key = type + "queueHooks";
            return jQuery._data(elem, key) || jQuery._data(elem, key, {
                    empty: jQuery.Callbacks("once memory").add(function () {
                        jQuery._removeData(elem, type + "queue");
                        jQuery._removeData(elem, key);
                    })
                });
        }
    });

    jQuery.fn.extend({
        queue: function (type, data) {
            var setter = 2;

            if (typeof type !== "string") {
                data = type;
                type = "fx";
                setter--;
            }

            if (arguments.length < setter) {
                return jQuery.queue(this[0], type);
            }

            return data === undefined ?
                this :
                this.each(function () {
                    var queue = jQuery.queue(this, type, data);

                    // ensure a hooks for this queue
                    jQuery._queueHooks(this, type);

                    if (type === "fx" && queue[0] !== "inprogress") {
                        jQuery.dequeue(this, type);
                    }
                });
        },
        dequeue: function (type) {
            return this.each(function () {
                jQuery.dequeue(this, type);
            });
        },
        clearQueue: function (type) {
            return this.queue(type || "fx", []);
        },
        // Get a promise resolved when queues of a certain type
        // are emptied (fx is the type by default)
        promise: function (type, obj) {
            var tmp,
                count = 1,
                defer = jQuery.Deferred(),
                elements = this,
                i = this.length,
                resolve = function () {
                    if (!( --count )) {
                        defer.resolveWith(elements, [elements]);
                    }
                };

            if (typeof type !== "string") {
                obj = type;
                type = undefined;
            }
            type = type || "fx";

            while (i--) {
                tmp = jQuery._data(elements[i], type + "queueHooks");
                if (tmp && tmp.empty) {
                    count++;
                    tmp.empty.add(resolve);
                }
            }
            resolve();
            return defer.promise(obj);
        }
    });


    /*
     * Helper functions for managing events -- not part of the public interface.
     * Props to Dean Edwards' addEvent library for many of the ideas.
     */
    jQuery.event = {

        global: {},

        add: function (elem, types, handler, data, selector) {
            var tmp, events, t, handleObjIn,
                special, eventHandle, handleObj,
                handlers, type, namespaces, origType,
                elemData = jQuery._data(elem);

            // Don't attach events to noData or text/comment nodes (but allow plain objects)
            if (!elemData) {
                return;
            }

            // Caller can pass in an object of custom data in lieu of the handler
            if (handler.handler) {
                handleObjIn = handler;
                handler = handleObjIn.handler;
                selector = handleObjIn.selector;
            }

            // Make sure that the handler has a unique ID, used to find/remove it later
            if (!handler.guid) {
                handler.guid = jQuery.guid++;
            }

            // Init the element's event structure and main handler, if this is the first
            if (!(events = elemData.events)) {
                events = elemData.events = {};
            }
            if (!(eventHandle = elemData.handle)) {
                eventHandle = elemData.handle = function (e) {
                    // Discard the second event of a jQuery.event.trigger() and
                    // when an event is called after a page has unloaded
                    return typeof jQuery !== strundefined && (!e || jQuery.event.triggered !== e.type) ?
                        jQuery.event.dispatch.apply(eventHandle.elem, arguments) :
                        undefined;
                };
                // Add elem as a property of the handle fn to prevent a memory leak with IE non-native events
                eventHandle.elem = elem;
            }

            // Handle multiple events separated by a space
            types = ( types || "" ).match(rnotwhite) || [""];
            t = types.length;
            while (t--) {
                tmp = rtypenamespace.exec(types[t]) || [];
                type = origType = tmp[1];
                namespaces = ( tmp[2] || "" ).split(".").sort();

                // There *must* be a type, no attaching namespace-only handlers
                if (!type) {
                    continue;
                }

                // If event changes its type, use the special event handlers for the changed type
                special = jQuery.event.special[type] || {};

                // If selector defined, determine special event api type, otherwise given type
                type = ( selector ? special.delegateType : special.bindType ) || type;

                // Update special based on newly reset type
                special = jQuery.event.special[type] || {};

                // handleObj is passed to all event handlers
                handleObj = jQuery.extend({
                    type: type,
                    origType: origType,
                    data: data,
                    handler: handler,
                    guid: handler.guid,
                    selector: selector,
                    needsContext: selector && jQuery.expr.match.needsContext.test(selector),
                    namespace: namespaces.join(".")
                }, handleObjIn);

                // Init the event handler queue if we're the first
                if (!(handlers = events[type])) {
                    handlers = events[type] = [];
                    handlers.delegateCount = 0;

                    // Only use addEventListener/attachEvent if the special events handler returns false
                    if (!special.setup || special.setup.call(elem, data, namespaces, eventHandle) === false) {
                        // Bind the global event handler to the element
                        if (elem.addEventListener) {
                            elem.addEventListener(type, eventHandle, false);

                        } else if (elem.attachEvent) {
                            elem.attachEvent("on" + type, eventHandle);
                        }
                    }
                }

                if (special.add) {
                    special.add.call(elem, handleObj);

                    if (!handleObj.handler.guid) {
                        handleObj.handler.guid = handler.guid;
                    }
                }

                // Add to the element's handler list, delegates in front
                if (selector) {
                    handlers.splice(handlers.delegateCount++, 0, handleObj);
                } else {
                    handlers.push(handleObj);
                }

                // Keep track of which events have ever been used, for event optimization
                jQuery.event.global[type] = true;
            }

            // Nullify elem to prevent memory leaks in IE
            elem = null;
        },

        // Detach an event or set of events from an element
        remove: function (elem, types, handler, selector, mappedTypes) {
            var j, handleObj, tmp,
                origCount, t, events,
                special, handlers, type,
                namespaces, origType,
                elemData = jQuery.hasData(elem) && jQuery._data(elem);

            if (!elemData || !(events = elemData.events)) {
                return;
            }

            // Once for each type.namespace in types; type may be omitted
            types = ( types || "" ).match(rnotwhite) || [""];
            t = types.length;
            while (t--) {
                tmp = rtypenamespace.exec(types[t]) || [];
                type = origType = tmp[1];
                namespaces = ( tmp[2] || "" ).split(".").sort();

                // Unbind all events (on this namespace, if provided) for the element
                if (!type) {
                    for (type in events) {
                        jQuery.event.remove(elem, type + types[t], handler, selector, true);
                    }
                    continue;
                }

                special = jQuery.event.special[type] || {};
                type = ( selector ? special.delegateType : special.bindType ) || type;
                handlers = events[type] || [];
                tmp = tmp[2] && new RegExp("(^|\\.)" + namespaces.join("\\.(?:.*\\.|)") + "(\\.|$)");

                // Remove matching events
                origCount = j = handlers.length;
                while (j--) {
                    handleObj = handlers[j];

                    if (( mappedTypes || origType === handleObj.origType ) &&
                        ( !handler || handler.guid === handleObj.guid ) &&
                        ( !tmp || tmp.test(handleObj.namespace) ) &&
                        ( !selector || selector === handleObj.selector || selector === "**" && handleObj.selector )) {
                        handlers.splice(j, 1);

                        if (handleObj.selector) {
                            handlers.delegateCount--;
                        }
                        if (special.remove) {
                            special.remove.call(elem, handleObj);
                        }
                    }
                }

                // Remove generic event handler if we removed something and no more handlers exist
                // (avoids potential for endless recursion during removal of special event handlers)
                if (origCount && !handlers.length) {
                    if (!special.teardown || special.teardown.call(elem, namespaces, elemData.handle) === false) {
                        jQuery.removeEvent(elem, type, elemData.handle);
                    }

                    delete events[type];
                }
            }

            // Remove the expando if it's no longer used
            if (jQuery.isEmptyObject(events)) {
                delete elemData.handle;

                // removeData also checks for emptiness and clears the expando if empty
                // so use it instead of delete
                jQuery._removeData(elem, "events");
            }
        },

        trigger: function (event, data, elem, onlyHandlers) {
            var handle, ontype, cur,
                bubbleType, special, tmp, i,
                eventPath = [elem || document],
                type = hasOwn.call(event, "type") ? event.type : event,
                namespaces = hasOwn.call(event, "namespace") ? event.namespace.split(".") : [];

            cur = tmp = elem = elem || document;

            // Don't do events on text and comment nodes
            if (elem.nodeType === 3 || elem.nodeType === 8) {
                return;
            }

            // focus/blur morphs to focusin/out; ensure we're not firing them right now
            if (rfocusMorph.test(type + jQuery.event.triggered)) {
                return;
            }

            if (type.indexOf(".") >= 0) {
                // Namespaced trigger; create a regexp to match event type in handle()
                namespaces = type.split(".");
                type = namespaces.shift();
                namespaces.sort();
            }
            ontype = type.indexOf(":") < 0 && "on" + type;

            // Caller can pass in a jQuery.Event object, Object, or just an event type string
            event = event[jQuery.expando] ?
                event :
                new jQuery.Event(type, typeof event === "object" && event);

            // Trigger bitmask: & 1 for native handlers; & 2 for jQuery (always true)
            event.isTrigger = onlyHandlers ? 2 : 3;
            event.namespace = namespaces.join(".");
            event.namespace_re = event.namespace ?
                new RegExp("(^|\\.)" + namespaces.join("\\.(?:.*\\.|)") + "(\\.|$)") :
                null;

            // Clean up the event in case it is being reused
            event.result = undefined;
            if (!event.target) {
                event.target = elem;
            }

            // Clone any incoming data and prepend the event, creating the handler arg list
            data = data == null ?
                [event] :
                jQuery.makeArray(data, [event]);

            // Allow special events to draw outside the lines
            special = jQuery.event.special[type] || {};
            if (!onlyHandlers && special.trigger && special.trigger.apply(elem, data) === false) {
                return;
            }

            // Determine event propagation path in advance, per W3C events spec (#9951)
            // Bubble up to document, then to window; watch for a global ownerDocument var (#9724)
            if (!onlyHandlers && !special.noBubble && !jQuery.isWindow(elem)) {

                bubbleType = special.delegateType || type;
                if (!rfocusMorph.test(bubbleType + type)) {
                    cur = cur.parentNode;
                }
                for (; cur; cur = cur.parentNode) {
                    eventPath.push(cur);
                    tmp = cur;
                }

                // Only add window if we got to document (e.g., not plain obj or detached DOM)
                if (tmp === (elem.ownerDocument || document)) {
                    eventPath.push(tmp.defaultView || tmp.parentWindow || window);
                }
            }

            // Fire handlers on the event path
            i = 0;
            while ((cur = eventPath[i++]) && !event.isPropagationStopped()) {

                event.type = i > 1 ?
                    bubbleType :
                    special.bindType || type;

                // jQuery handler
                handle = ( jQuery._data(cur, "events") || {} )[event.type] && jQuery._data(cur, "handle");
                if (handle) {
                    handle.apply(cur, data);
                }

                // Native handler
                handle = ontype && cur[ontype];
                if (handle && handle.apply && jQuery.acceptData(cur)) {
                    event.result = handle.apply(cur, data);
                    if (event.result === false) {
                        event.preventDefault();
                    }
                }
            }
            event.type = type;

            // If nobody prevented the default action, do it now
            if (!onlyHandlers && !event.isDefaultPrevented()) {

                if ((!special._default || special._default.apply(eventPath.pop(), data) === false) &&
                    jQuery.acceptData(elem)) {

                    // Call a native DOM method on the target with the same name name as the event.
                    // Can't use an .isFunction() check here because IE6/7 fails that test.
                    // Don't do default actions on window, that's where global variables be (#6170)
                    if (ontype && elem[type] && !jQuery.isWindow(elem)) {

                        // Don't re-trigger an onFOO event when we call its FOO() method
                        tmp = elem[ontype];

                        if (tmp) {
                            elem[ontype] = null;
                        }

                        // Prevent re-triggering of the same event, since we already bubbled it above
                        jQuery.event.triggered = type;
                        try {
                            elem[type]();
                        } catch (e) {
                            // IE<9 dies on focus/blur to hidden element (#1486,#12518)
                            // only reproducible on winXP IE8 native, not IE9 in IE8 mode
                        }
                        jQuery.event.triggered = undefined;

                        if (tmp) {
                            elem[ontype] = tmp;
                        }
                    }
                }
            }

            return event.result;
        },

        dispatch: function (event) {

            // Make a writable jQuery.Event from the native event object
            event = jQuery.event.fix(event);

            var i, ret, handleObj, matched, j,
                handlerQueue = [],
                args = slice.call(arguments),
                handlers = ( jQuery._data(this, "events") || {} )[event.type] || [],
                special = jQuery.event.special[event.type] || {};

            // Use the fix-ed jQuery.Event rather than the (read-only) native event
            args[0] = event;
            event.delegateTarget = this;

            // Call the preDispatch hook for the mapped type, and let it bail if desired
            if (special.preDispatch && special.preDispatch.call(this, event) === false) {
                return;
            }

            // Determine handlers
            handlerQueue = jQuery.event.handlers.call(this, event, handlers);

            // Run delegates first; they may want to stop propagation beneath us
            i = 0;
            while ((matched = handlerQueue[i++]) && !event.isPropagationStopped()) {
                event.currentTarget = matched.elem;

                j = 0;
                while ((handleObj = matched.handlers[j++]) && !event.isImmediatePropagationStopped()) {

                    // Triggered event must either 1) have no namespace, or
                    // 2) have namespace(s) a subset or equal to those in the bound event (both can have no namespace).
                    if (!event.namespace_re || event.namespace_re.test(handleObj.namespace)) {

                        event.handleObj = handleObj;
                        event.data = handleObj.data;

                        ret = ( (jQuery.event.special[handleObj.origType] || {}).handle || handleObj.handler )
                            .apply(matched.elem, args);

                        if (ret !== undefined) {
                            if ((event.result = ret) === false) {
                                event.preventDefault();
                                event.stopPropagation();
                            }
                        }
                    }
                }
            }

            // Call the postDispatch hook for the mapped type
            if (special.postDispatch) {
                special.postDispatch.call(this, event);
            }

            return event.result;
        },

        handlers: function (event, handlers) {
            var sel, handleObj, matches, i,
                handlerQueue = [],
                delegateCount = handlers.delegateCount,
                cur = event.target;

            // Find delegate handlers
            // Black-hole SVG <use> instance trees (#13180)
            // Avoid non-left-click bubbling in Firefox (#3861)
            if (delegateCount && cur.nodeType && (!event.button || event.type !== "click")) {

                /* jshint eqeqeq: false */
                for (; cur != this; cur = cur.parentNode || this) {
                    /* jshint eqeqeq: true */

                    // Don't check non-elements (#13208)
                    // Don't process clicks on disabled elements (#6911, #8165, #11382, #11764)
                    if (cur.nodeType === 1 && (cur.disabled !== true || event.type !== "click")) {
                        matches = [];
                        for (i = 0; i < delegateCount; i++) {
                            handleObj = handlers[i];

                            // Don't conflict with Object.prototype properties (#13203)
                            sel = handleObj.selector + " ";

                            if (matches[sel] === undefined) {
                                matches[sel] = handleObj.needsContext ?
                                    jQuery(sel, this).index(cur) >= 0 :
                                    jQuery.find(sel, this, null, [cur]).length;
                            }
                            if (matches[sel]) {
                                matches.push(handleObj);
                            }
                        }
                        if (matches.length) {
                            handlerQueue.push({elem: cur, handlers: matches});
                        }
                    }
                }
            }

            // Add the remaining (directly-bound) handlers
            if (delegateCount < handlers.length) {
                handlerQueue.push({elem: this, handlers: handlers.slice(delegateCount)});
            }

            return handlerQueue;
        },

        fix: function (event) {
            if (event[jQuery.expando]) {
                return event;
            }

            // Create a writable copy of the event object and normalize some properties
            var i, prop, copy,
                type = event.type,
                originalEvent = event,
                fixHook = this.fixHooks[type];

            if (!fixHook) {
                this.fixHooks[type] = fixHook =
                    rmouseEvent.test(type) ? this.mouseHooks :
                        rkeyEvent.test(type) ? this.keyHooks :
                            {};
            }
            copy = fixHook.props ? this.props.concat(fixHook.props) : this.props;

            event = new jQuery.Event(originalEvent);

            i = copy.length;
            while (i--) {
                prop = copy[i];
                event[prop] = originalEvent[prop];
            }

            // Support: IE<9
            // Fix target property (#1925)
            if (!event.target) {
                event.target = originalEvent.srcElement || document;
            }

            // Support: Chrome 23+, Safari?
            // Target should not be a text node (#504, #13143)
            if (event.target.nodeType === 3) {
                event.target = event.target.parentNode;
            }

            // Support: IE<9
            // For mouse/key events, metaKey==false if it's undefined (#3368, #11328)
            event.metaKey = !!event.metaKey;

            return fixHook.filter ? fixHook.filter(event, originalEvent) : event;
        },

        // Includes some event props shared by KeyEvent and MouseEvent
        props: "altKey bubbles cancelable ctrlKey currentTarget eventPhase metaKey relatedTarget shiftKey target timeStamp view which".split(" "),

        fixHooks: {},

        keyHooks: {
            props: "char charCode key keyCode".split(" "),
            filter: function (event, original) {

                // Add which for key events
                if (event.which == null) {
                    event.which = original.charCode != null ? original.charCode : original.keyCode;
                }

                return event;
            }
        },

        mouseHooks: {
            props: "button buttons clientX clientY fromElement offsetX offsetY pageX pageY screenX screenY toElement".split(" "),
            filter: function (event, original) {
                var body, eventDoc, doc,
                    button = original.button,
                    fromElement = original.fromElement;

                // Calculate pageX/Y if missing and clientX/Y available
                if (event.pageX == null && original.clientX != null) {
                    eventDoc = event.target.ownerDocument || document;
                    doc = eventDoc.documentElement;
                    body = eventDoc.body;

                    event.pageX = original.clientX + ( doc && doc.scrollLeft || body && body.scrollLeft || 0 ) - ( doc && doc.clientLeft || body && body.clientLeft || 0 );
                    event.pageY = original.clientY + ( doc && doc.scrollTop || body && body.scrollTop || 0 ) - ( doc && doc.clientTop || body && body.clientTop || 0 );
                }

                // Add relatedTarget, if necessary
                if (!event.relatedTarget && fromElement) {
                    event.relatedTarget = fromElement === event.target ? original.toElement : fromElement;
                }

                // Add which for click: 1 === left; 2 === middle; 3 === right
                // Note: button is not normalized, so don't use it
                if (!event.which && button !== undefined) {
                    event.which = ( button & 1 ? 1 : ( button & 2 ? 3 : ( button & 4 ? 2 : 0 ) ) );
                }

                return event;
            }
        },

        special: {
            load: {
                // Prevent triggered image.load events from bubbling to window.load
                noBubble: true
            },
            focus: {
                // Fire native event if possible so blur/focus sequence is correct
                trigger: function () {
                    if (this !== safeActiveElement() && this.focus) {
                        try {
                            this.focus();
                            return false;
                        } catch (e) {
                            // Support: IE<9
                            // If we error on focus to hidden element (#1486, #12518),
                            // let .trigger() run the handlers
                        }
                    }
                },
                delegateType: "focusin"
            },
            blur: {
                trigger: function () {
                    if (this === safeActiveElement() && this.blur) {
                        this.blur();
                        return false;
                    }
                },
                delegateType: "focusout"
            },
            click: {
                // For checkbox, fire native event so checked state will be right
                trigger: function () {
                    if (jQuery.nodeName(this, "input") && this.type === "checkbox" && this.click) {
                        this.click();
                        return false;
                    }
                },

                // For cross-browser consistency, don't fire native .click() on links
                _default: function (event) {
                    return jQuery.nodeName(event.target, "a");
                }
            },

            beforeunload: {
                postDispatch: function (event) {

                    // Support: Firefox 20+
                    // Firefox doesn't alert if the returnValue field is not set.
                    if (event.result !== undefined && event.originalEvent) {
                        event.originalEvent.returnValue = event.result;
                    }
                }
            }
        },

        simulate: function (type, elem, event, bubble) {
            // Piggyback on a donor event to simulate a different one.
            // Fake originalEvent to avoid donor's stopPropagation, but if the
            // simulated event prevents default then we do the same on the donor.
            var e = jQuery.extend(
                new jQuery.Event(),
                event,
                {
                    type: type,
                    isSimulated: true,
                    originalEvent: {}
                }
            );
            if (bubble) {
                jQuery.event.trigger(e, null, elem);
            } else {
                jQuery.event.dispatch.call(elem, e);
            }
            if (e.isDefaultPrevented()) {
                event.preventDefault();
            }
        }
    };

    jQuery.removeEvent = document.removeEventListener ?
        function (elem, type, handle) {
            if (elem.removeEventListener) {
                elem.removeEventListener(type, handle, false);
            }
        } :
        function (elem, type, handle) {
            var name = "on" + type;

            if (elem.detachEvent) {

                // #8545, #7054, preventing memory leaks for custom events in IE6-8
                // detachEvent needed property on element, by name of that event, to properly expose it to GC
                if (typeof elem[name] === strundefined) {
                    elem[name] = null;
                }

                elem.detachEvent(name, handle);
            }
        };

    jQuery.Event = function (src, props) {
        // Allow instantiation without the 'new' keyword
        if (!(this instanceof jQuery.Event)) {
            return new jQuery.Event(src, props);
        }

        // Event object
        if (src && src.type) {
            this.originalEvent = src;
            this.type = src.type;

            // Events bubbling up the document may have been marked as prevented
            // by a handler lower down the tree; reflect the correct value.
            this.isDefaultPrevented = src.defaultPrevented ||
            src.defaultPrevented === undefined &&
            // Support: IE < 9, Android < 4.0
            src.returnValue === false ?
                returnTrue :
                returnFalse;

            // Event type
        } else {
            this.type = src;
        }

        // Put explicitly provided properties onto the event object
        if (props) {
            jQuery.extend(this, props);
        }

        // Create a timestamp if incoming event doesn't have one
        this.timeStamp = src && src.timeStamp || jQuery.now();

        // Mark it as fixed
        this[jQuery.expando] = true;
    };

    var rformElems = /^(?:input|select|textarea)$/i,
        rkeyEvent = /^key/,
        rmouseEvent = /^(?:mouse|pointer|contextmenu)|click/,
        rfocusMorph = /^(?:focusinfocus|focusoutblur)$/,
        rtypenamespace = /^([^.]*)(?:\.(.+)|)$/;

    function returnTrue() {
        return true;
    }

    function returnFalse() {
        return false;
    }

// jQuery.Event is based on DOM3 Events as specified by the ECMAScript Language Binding
// http://www.w3.org/TR/2003/WD-DOM-Level-3-Events-20030331/ecma-script-binding.html
    jQuery.Event.prototype = {
        isDefaultPrevented: returnFalse,
        isPropagationStopped: returnFalse,
        isImmediatePropagationStopped: returnFalse,

        preventDefault: function () {
            var e = this.originalEvent;

            this.isDefaultPrevented = returnTrue;
            if (!e) {
                return;
            }

            // If preventDefault exists, run it on the original event
            if (e.preventDefault) {
                e.preventDefault();

                // Support: IE
                // Otherwise set the returnValue property of the original event to false
            } else {
                e.returnValue = false;
            }
        },
        stopPropagation: function () {
            var e = this.originalEvent;

            this.isPropagationStopped = returnTrue;
            if (!e) {
                return;
            }
            // If stopPropagation exists, run it on the original event
            if (e.stopPropagation) {
                e.stopPropagation();
            }

            // Support: IE
            // Set the cancelBubble property of the original event to true
            e.cancelBubble = true;
        },
        stopImmediatePropagation: function () {
            var e = this.originalEvent;

            this.isImmediatePropagationStopped = returnTrue;

            if (e && e.stopImmediatePropagation) {
                e.stopImmediatePropagation();
            }

            this.stopPropagation();
        }
    };


// IE submit delegation
    if (!support.submitBubbles) {

        jQuery.event.special.submit = {
            setup: function () {
                // Only need this for delegated form submit events
                if (jQuery.nodeName(this, "form")) {
                    return false;
                }

                // Lazy-add a submit handler when a descendant form may potentially be submitted
                jQuery.event.add(this, "click._submit keypress._submit", function (e) {
                    // Node name check avoids a VML-related crash in IE (#9807)
                    var elem = e.target,
                        form = jQuery.nodeName(elem, "input") || jQuery.nodeName(elem, "button") ? elem.form : undefined;
                    if (form && !jQuery._data(form, "submitBubbles")) {
                        jQuery.event.add(form, "submit._submit", function (event) {
                            event._submit_bubble = true;
                        });
                        jQuery._data(form, "submitBubbles", true);
                    }
                });
                // return undefined since we don't need an event listener
            },

            postDispatch: function (event) {
                // If form was submitted by the user, bubble the event up the tree
                if (event._submit_bubble) {
                    delete event._submit_bubble;
                    if (this.parentNode && !event.isTrigger) {
                        jQuery.event.simulate("submit", this.parentNode, event, true);
                    }
                }
            },

            teardown: function () {
                // Only need this for delegated form submit events
                if (jQuery.nodeName(this, "form")) {
                    return false;
                }

                // Remove delegated handlers; cleanData eventually reaps submit handlers attached above
                jQuery.event.remove(this, "._submit");
            }
        };
    }

// IE change delegation and checkbox/radio fix
    if (!support.changeBubbles) {

        jQuery.event.special.change = {

            setup: function () {

                if (rformElems.test(this.nodeName)) {
                    // IE doesn't fire change on a check/radio until blur; trigger it on click
                    // after a propertychange. Eat the blur-change in special.change.handle.
                    // This still fires onchange a second time for check/radio after blur.
                    if (this.type === "checkbox" || this.type === "radio") {
                        jQuery.event.add(this, "propertychange._change", function (event) {
                            if (event.originalEvent.propertyName === "checked") {
                                this._just_changed = true;
                            }
                        });
                        jQuery.event.add(this, "click._change", function (event) {
                            if (this._just_changed && !event.isTrigger) {
                                this._just_changed = false;
                            }
                            // Allow triggered, simulated change events (#11500)
                            jQuery.event.simulate("change", this, event, true);
                        });
                    }
                    return false;
                }
                // Delegated event; lazy-add a change handler on descendant inputs
                jQuery.event.add(this, "beforeactivate._change", function (e) {
                    var elem = e.target;

                    if (rformElems.test(elem.nodeName) && !jQuery._data(elem, "changeBubbles")) {
                        jQuery.event.add(elem, "change._change", function (event) {
                            if (this.parentNode && !event.isSimulated && !event.isTrigger) {
                                jQuery.event.simulate("change", this.parentNode, event, true);
                            }
                        });
                        jQuery._data(elem, "changeBubbles", true);
                    }
                });
            },

            handle: function (event) {
                var elem = event.target;

                // Swallow native change events from checkbox/radio, we already triggered them above
                if (this !== elem || event.isSimulated || event.isTrigger || (elem.type !== "radio" && elem.type !== "checkbox")) {
                    return event.handleObj.handler.apply(this, arguments);
                }
            },

            teardown: function () {
                jQuery.event.remove(this, "._change");

                return !rformElems.test(this.nodeName);
            }
        };
    }

// Create "bubbling" focus and blur events
    if (!support.focusinBubbles) {
        jQuery.each({focus: "focusin", blur: "focusout"}, function (orig, fix) {

            // Attach a single capturing handler on the document while someone wants focusin/focusout
            var handler = function (event) {
                jQuery.event.simulate(fix, event.target, jQuery.event.fix(event), true);
            };

            jQuery.event.special[fix] = {
                setup: function () {
                    var doc = this.ownerDocument || this,
                        attaches = jQuery._data(doc, fix);

                    if (!attaches) {
                        doc.addEventListener(orig, handler, true);
                    }
                    jQuery._data(doc, fix, ( attaches || 0 ) + 1);
                },
                teardown: function () {
                    var doc = this.ownerDocument || this,
                        attaches = jQuery._data(doc, fix) - 1;

                    if (!attaches) {
                        doc.removeEventListener(orig, handler, true);
                        jQuery._removeData(doc, fix);
                    } else {
                        jQuery._data(doc, fix, attaches);
                    }
                }
            };
        });
    }

    jQuery.fn.extend({

        on: function (types, selector, data, fn, /*INTERNAL*/ one) {
            var type, origFn;

            // Types can be a map of types/handlers
            if (typeof types === "object") {
                // ( types-Object, selector, data )
                if (typeof selector !== "string") {
                    // ( types-Object, data )
                    data = data || selector;
                    selector = undefined;
                }
                for (type in types) {
                    this.on(type, selector, data, types[type], one);
                }
                return this;
            }

            if (data == null && fn == null) {
                // ( types, fn )
                fn = selector;
                data = selector = undefined;
            } else if (fn == null) {
                if (typeof selector === "string") {
                    // ( types, selector, fn )
                    fn = data;
                    data = undefined;
                } else {
                    // ( types, data, fn )
                    fn = data;
                    data = selector;
                    selector = undefined;
                }
            }
            if (fn === false) {
                fn = returnFalse;
            } else if (!fn) {
                return this;
            }

            if (one === 1) {
                origFn = fn;
                fn = function (event) {
                    // Can use an empty set, since event contains the info
                    jQuery().off(event);
                    return origFn.apply(this, arguments);
                };
                // Use same guid so caller can remove using origFn
                fn.guid = origFn.guid || ( origFn.guid = jQuery.guid++ );
            }
            return this.each(function () {
                jQuery.event.add(this, types, fn, data, selector);
            });
        },
        one: function (types, selector, data, fn) {
            return this.on(types, selector, data, fn, 1);
        },
        off: function (types, selector, fn) {
            var handleObj, type;
            if (types && types.preventDefault && types.handleObj) {
                // ( event )  dispatched jQuery.Event
                handleObj = types.handleObj;
                jQuery(types.delegateTarget).off(
                    handleObj.namespace ? handleObj.origType + "." + handleObj.namespace : handleObj.origType,
                    handleObj.selector,
                    handleObj.handler
                );
                return this;
            }
            if (typeof types === "object") {
                // ( types-object [, selector] )
                for (type in types) {
                    this.off(type, selector, types[type]);
                }
                return this;
            }
            if (selector === false || typeof selector === "function") {
                // ( types [, fn] )
                fn = selector;
                selector = undefined;
            }
            if (fn === false) {
                fn = returnFalse;
            }
            return this.each(function () {
                jQuery.event.remove(this, types, fn, selector);
            });
        },

        trigger: function (type, data) {
            return this.each(function () {
                jQuery.event.trigger(type, data, this);
            });
        },
        triggerHandler: function (type, data) {
            var elem = this[0];
            if (elem) {
                return jQuery.event.trigger(type, data, elem, true);
            }
        }
    });


// Based off of the plugin by Clint Helfers, with permission.
// http://blindsignals.com/index.php/2009/07/jquery-delay/
    jQuery.fn.delay = function (time, type) {
        time = jQuery.fx ? jQuery.fx.speeds[time] || time : time;
        type = type || "fx";

        return this.queue(type, function (next, hooks) {
            var timeout = setTimeout(next, time);
            hooks.stop = function () {
                clearTimeout(timeout);
            };
        });
    };


    var nonce = jQuery.now();

    var rquery = (/\?/);


    var rvalidtokens = /(,)|(\[|{)|(}|])|"(?:[^"\\\r\n]|\\["\\\/bfnrt]|\\u[\da-fA-F]{4})*"\s*:?|true|false|null|-?(?!0\d)\d+(?:\.\d+|)(?:[eE][+-]?\d+|)/g;

    jQuery.parseJSON = function (data) {
        // Attempt to parse using the native JSON parser first
        if (window.JSON && window.JSON.parse) {
            // Support: Android 2.3
            // Workaround failure to string-cast null input
            return window.JSON.parse(data + "");
        }

        var requireNonComma,
            depth = null,
            str = jQuery.trim(data + "");

        // Guard against invalid (and possibly dangerous) input by ensuring that nothing remains
        // after removing valid tokens
        return str && !jQuery.trim(str.replace(rvalidtokens, function (token, comma, open, close) {

            // Force termination if we see a misplaced comma
            if (requireNonComma && comma) {
                depth = 0;
            }

            // Perform no more replacements after returning to outermost depth
            if (depth === 0) {
                return token;
            }

            // Commas must not follow "[", "{", or ","
            requireNonComma = open || comma;

            // Determine new depth
            // array/object open ("[" or "{"): depth += true - false (increment)
            // array/object close ("]" or "}"): depth += false - true (decrement)
            // other cases ("," or primitive): depth += true - true (numeric cast)
            depth += !close - !open;

            // Remove this token
            return "";
        })) ?
            ( Function("return " + str) )() :
            jQuery.error("Invalid JSON: " + data);
    };


// Cross-browser xml parsing
    jQuery.parseXML = function (data) {
        var xml, tmp;
        if (!data || typeof data !== "string") {
            return null;
        }
        try {
            if (window.DOMParser) { // Standard
                tmp = new DOMParser();
                xml = tmp.parseFromString(data, "text/xml");
            } else { // IE
                xml = new ActiveXObject("Microsoft.XMLDOM");
                xml.async = "false";
                xml.loadXML(data);
            }
        } catch (e) {
            xml = undefined;
        }
        if (!xml || !xml.documentElement || xml.getElementsByTagName("parsererror").length) {
            jQuery.error("Invalid XML: " + data);
        }
        return xml;
    };


    var
        // Document location
        ajaxLocParts,
        ajaxLocation,

        rhash = /#.*$/,
        rts = /([?&])_=[^&]*/,
        rheaders = /^(.*?):[ \t]*([^\r\n]*)\r?$/mg, // IE leaves an \r character at EOL
        // #7653, #8125, #8152: local protocol detection
        rlocalProtocol = /^(?:about|app|app-storage|.+-extension|file|res|widget):$/,
        rnoContent = /^(?:GET|HEAD)$/,
        rprotocol = /^\/\//,
        rurl = /^([\w.+-]+:)(?:\/\/(?:[^\/?#]*@|)([^\/?#:]*)(?::(\d+)|)|)/,

        /* Prefilters
         * 1) They are useful to introduce custom dataTypes (see ajax/jsonp.js for an example)
         * 2) These are called:
         *    - BEFORE asking for a transport
         *    - AFTER param serialization (s.data is a string if s.processData is true)
         * 3) key is the dataType
         * 4) the catchall symbol "*" can be used
         * 5) execution will start with transport dataType and THEN continue down to "*" if needed
         */
        prefilters = {},

        /* Transports bindings
         * 1) key is the dataType
         * 2) the catchall symbol "*" can be used
         * 3) selection will start with transport dataType and THEN go to "*" if needed
         */
        transports = {},

        // Avoid comment-prolog char sequence (#10098); must appease lint and evade compression
        allTypes = "*/".concat("*");

// #8138, IE may throw an exception when accessing
// a field from window.location if document.domain has been set
    try {
        ajaxLocation = location.href;
    } catch (e) {
        // Use the href attribute of an A element
        // since IE will modify it given document.location
        ajaxLocation = document.createElement("a");
        ajaxLocation.href = "";
        ajaxLocation = ajaxLocation.href;
    }

// Segment location into parts
    ajaxLocParts = rurl.exec(ajaxLocation.toLowerCase()) || [];

// Base "constructor" for jQuery.ajaxPrefilter and jQuery.ajaxTransport
    function addToPrefiltersOrTransports(structure) {

        // dataTypeExpression is optional and defaults to "*"
        return function (dataTypeExpression, func) {

            if (typeof dataTypeExpression !== "string") {
                func = dataTypeExpression;
                dataTypeExpression = "*";
            }

            var dataType,
                i = 0,
                dataTypes = dataTypeExpression.toLowerCase().match(rnotwhite) || [];

            if (jQuery.isFunction(func)) {
                // For each dataType in the dataTypeExpression
                while ((dataType = dataTypes[i++])) {
                    // Prepend if requested
                    if (dataType.charAt(0) === "+") {
                        dataType = dataType.slice(1) || "*";
                        (structure[dataType] = structure[dataType] || []).unshift(func);

                        // Otherwise append
                    } else {
                        (structure[dataType] = structure[dataType] || []).push(func);
                    }
                }
            }
        };
    }

// Base inspection function for prefilters and transports
    function inspectPrefiltersOrTransports(structure, options, originalOptions, jqXHR) {

        var inspected = {},
            seekingTransport = ( structure === transports );

        function inspect(dataType) {
            var selected;
            inspected[dataType] = true;
            jQuery.each(structure[dataType] || [], function (_, prefilterOrFactory) {
                var dataTypeOrTransport = prefilterOrFactory(options, originalOptions, jqXHR);
                if (typeof dataTypeOrTransport === "string" && !seekingTransport && !inspected[dataTypeOrTransport]) {
                    options.dataTypes.unshift(dataTypeOrTransport);
                    inspect(dataTypeOrTransport);
                    return false;
                } else if (seekingTransport) {
                    return !( selected = dataTypeOrTransport );
                }
            });
            return selected;
        }

        return inspect(options.dataTypes[0]) || !inspected["*"] && inspect("*");
    }

// A special extend for ajax options
// that takes "flat" options (not to be deep extended)
// Fixes #9887
    function ajaxExtend(target, src) {
        var deep, key,
            flatOptions = jQuery.ajaxSettings.flatOptions || {};

        for (key in src) {
            if (src[key] !== undefined) {
                ( flatOptions[key] ? target : ( deep || (deep = {}) ) )[key] = src[key];
            }
        }
        if (deep) {
            jQuery.extend(true, target, deep);
        }

        return target;
    }

    /* Handles responses to an ajax request:
     * - finds the right dataType (mediates between content-type and expected dataType)
     * - returns the corresponding response
     */
    function ajaxHandleResponses(s, jqXHR, responses) {
        var firstDataType, ct, finalDataType, type,
            contents = s.contents,
            dataTypes = s.dataTypes;

        // Remove auto dataType and get content-type in the process
        while (dataTypes[0] === "*") {
            dataTypes.shift();
            if (ct === undefined) {
                ct = s.mimeType || jqXHR.getResponseHeader("Content-Type");
            }
        }

        // Check if we're dealing with a known content-type
        if (ct) {
            for (type in contents) {
                if (contents[type] && contents[type].test(ct)) {
                    dataTypes.unshift(type);
                    break;
                }
            }
        }

        // Check to see if we have a response for the expected dataType
        if (dataTypes[0] in responses) {
            finalDataType = dataTypes[0];
        } else {
            // Try convertible dataTypes
            for (type in responses) {
                if (!dataTypes[0] || s.converters[type + " " + dataTypes[0]]) {
                    finalDataType = type;
                    break;
                }
                if (!firstDataType) {
                    firstDataType = type;
                }
            }
            // Or just use first one
            finalDataType = finalDataType || firstDataType;
        }

        // If we found a dataType
        // We add the dataType to the list if needed
        // and return the corresponding response
        if (finalDataType) {
            if (finalDataType !== dataTypes[0]) {
                dataTypes.unshift(finalDataType);
            }
            return responses[finalDataType];
        }
    }

    /* Chain conversions given the request and the original response
     * Also sets the responseXXX fields on the jqXHR instance
     */
    function ajaxConvert(s, response, jqXHR, isSuccess) {
        var conv2, current, conv, tmp, prev,
            converters = {},
            // Work with a copy of dataTypes in case we need to modify it for conversion
            dataTypes = s.dataTypes.slice();

        // Create converters map with lowercased keys
        if (dataTypes[1]) {
            for (conv in s.converters) {
                converters[conv.toLowerCase()] = s.converters[conv];
            }
        }

        current = dataTypes.shift();

        // Convert to each sequential dataType
        while (current) {

            if (s.responseFields[current]) {
                jqXHR[s.responseFields[current]] = response;
            }

            // Apply the dataFilter if provided
            if (!prev && isSuccess && s.dataFilter) {
                response = s.dataFilter(response, s.dataType);
            }

            prev = current;
            current = dataTypes.shift();

            if (current) {

                // There's only work to do if current dataType is non-auto
                if (current === "*") {

                    current = prev;

                    // Convert response if prev dataType is non-auto and differs from current
                } else if (prev !== "*" && prev !== current) {

                    // Seek a direct converter
                    conv = converters[prev + " " + current] || converters["* " + current];

                    // If none found, seek a pair
                    if (!conv) {
                        for (conv2 in converters) {

                            // If conv2 outputs current
                            tmp = conv2.split(" ");
                            if (tmp[1] === current) {

                                // If prev can be converted to accepted input
                                conv = converters[prev + " " + tmp[0]] ||
                                    converters["* " + tmp[0]];
                                if (conv) {
                                    // Condense equivalence converters
                                    if (conv === true) {
                                        conv = converters[conv2];

                                        // Otherwise, insert the intermediate dataType
                                    } else if (converters[conv2] !== true) {
                                        current = tmp[0];
                                        dataTypes.unshift(tmp[1]);
                                    }
                                    break;
                                }
                            }
                        }
                    }

                    // Apply converter (if not an equivalence)
                    if (conv !== true) {

                        // Unless errors are allowed to bubble, catch and return them
                        if (conv && s["throws"]) {
                            response = conv(response);
                        } else {
                            try {
                                response = conv(response);
                            } catch (e) {
                                return {
                                    state: "parsererror",
                                    error: conv ? e : "No conversion from " + prev + " to " + current
                                };
                            }
                        }
                    }
                }
            }
        }

        return {state: "success", data: response};
    }

    jQuery.extend({

        // Counter for holding the number of active queries
        active: 0,

        // Last-Modified header cache for next request
        lastModified: {},
        etag: {},

        ajaxSettings: {
            url: ajaxLocation,
            type: "GET",
            isLocal: rlocalProtocol.test(ajaxLocParts[1]),
            global: true,
            processData: true,
            async: true,
            contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            /*
             timeout: 0,
             data: null,
             dataType: null,
             username: null,
             password: null,
             cache: null,
             throws: false,
             traditional: false,
             headers: {},
             */

            accepts: {
                "*": allTypes,
                text: "text/plain",
                html: "text/html",
                xml: "application/xml, text/xml",
                json: "application/json, text/javascript"
            },

            contents: {
                xml: /xml/,
                html: /html/,
                json: /json/
            },

            responseFields: {
                xml: "responseXML",
                text: "responseText",
                json: "responseJSON"
            },

            // Data converters
            // Keys separate source (or catchall "*") and destination types with a single space
            converters: {

                // Convert anything to text
                "* text": String,

                // Text to html (true = no transformation)
                "text html": true,

                // Evaluate text as a json expression
                "text json": jQuery.parseJSON,

                // Parse text as xml
                "text xml": jQuery.parseXML
            },

            // For options that shouldn't be deep extended:
            // you can add your own custom options here if
            // and when you create one that shouldn't be
            // deep extended (see ajaxExtend)
            flatOptions: {
                url: true,
                context: true
            }
        },

        // Creates a full fledged settings object into target
        // with both ajaxSettings and settings fields.
        // If target is omitted, writes into ajaxSettings.
        ajaxSetup: function (target, settings) {
            return settings ?

                // Building a settings object
                ajaxExtend(ajaxExtend(target, jQuery.ajaxSettings), settings) :

                // Extending ajaxSettings
                ajaxExtend(jQuery.ajaxSettings, target);
        },

        ajaxPrefilter: addToPrefiltersOrTransports(prefilters),
        ajaxTransport: addToPrefiltersOrTransports(transports),

        // Main method
        ajax: function (url, options) {

            // If url is an object, simulate pre-1.5 signature
            if (typeof url === "object") {
                options = url;
                url = undefined;
            }

            // Force options to be an object
            options = options || {};

            var // Cross-domain detection vars
                parts,
                // Loop variable
                i,
                // URL without anti-cache param
                cacheURL,
                // Response headers as string
                responseHeadersString,
                // timeout handle
                timeoutTimer,

                // To know if global events are to be dispatched
                fireGlobals,

                transport,
                // Response headers
                responseHeaders,
                // Create the final options object
                s = jQuery.ajaxSetup({}, options),
                // Callbacks context
                callbackContext = s.context || s,
                // Context for global events is callbackContext if it is a DOM node or jQuery collection
                globalEventContext = s.context && ( callbackContext.nodeType || callbackContext.jquery ) ?
                    jQuery(callbackContext) :
                    jQuery.event,
                // Deferreds
                deferred = jQuery.Deferred(),
                completeDeferred = jQuery.Callbacks("once memory"),
                // Status-dependent callbacks
                statusCode = s.statusCode || {},
                // Headers (they are sent all at once)
                requestHeaders = {},
                requestHeadersNames = {},
                // The jqXHR state
                state = 0,
                // Default abort message
                strAbort = "canceled",
                // Fake xhr
                jqXHR = {
                    readyState: 0,

                    // Builds headers hashtable if needed
                    getResponseHeader: function (key) {
                        var match;
                        if (state === 2) {
                            if (!responseHeaders) {
                                responseHeaders = {};
                                while ((match = rheaders.exec(responseHeadersString))) {
                                    responseHeaders[match[1].toLowerCase()] = match[2];
                                }
                            }
                            match = responseHeaders[key.toLowerCase()];
                        }
                        return match == null ? null : match;
                    },

                    // Raw string
                    getAllResponseHeaders: function () {
                        return state === 2 ? responseHeadersString : null;
                    },

                    // Caches the header
                    setRequestHeader: function (name, value) {
                        var lname = name.toLowerCase();
                        if (!state) {
                            name = requestHeadersNames[lname] = requestHeadersNames[lname] || name;
                            requestHeaders[name] = value;
                        }
                        return this;
                    },

                    // Overrides response content-type header
                    overrideMimeType: function (type) {
                        if (!state) {
                            s.mimeType = type;
                        }
                        return this;
                    },

                    // Status-dependent callbacks
                    statusCode: function (map) {
                        var code;
                        if (map) {
                            if (state < 2) {
                                for (code in map) {
                                    // Lazy-add the new callback in a way that preserves old ones
                                    statusCode[code] = [statusCode[code], map[code]];
                                }
                            } else {
                                // Execute the appropriate callbacks
                                jqXHR.always(map[jqXHR.status]);
                            }
                        }
                        return this;
                    },

                    // Cancel the request
                    abort: function (statusText) {
                        var finalText = statusText || strAbort;
                        if (transport) {
                            transport.abort(finalText);
                        }
                        done(0, finalText);
                        return this;
                    }
                };

            // Attach deferreds
            deferred.promise(jqXHR).complete = completeDeferred.add;
            jqXHR.success = jqXHR.done;
            jqXHR.error = jqXHR.fail;

            // Remove hash character (#7531: and string promotion)
            // Add protocol if not provided (#5866: IE7 issue with protocol-less urls)
            // Handle falsy url in the settings object (#10093: consistency with old signature)
            // We also use the url parameter if available
            s.url = ( ( url || s.url || ajaxLocation ) + "" ).replace(rhash, "").replace(rprotocol, ajaxLocParts[1] + "//");

            // Alias method option to type as per ticket #12004
            s.type = options.method || options.type || s.method || s.type;

            // Extract dataTypes list
            s.dataTypes = jQuery.trim(s.dataType || "*").toLowerCase().match(rnotwhite) || [""];

            // A cross-domain request is in order when we have a protocol:host:port mismatch
            if (s.crossDomain == null) {
                parts = rurl.exec(s.url.toLowerCase());
                s.crossDomain = !!( parts &&
                    ( parts[1] !== ajaxLocParts[1] || parts[2] !== ajaxLocParts[2] ||
                    ( parts[3] || ( parts[1] === "http:" ? "80" : "443" ) ) !==
                    ( ajaxLocParts[3] || ( ajaxLocParts[1] === "http:" ? "80" : "443" ) ) )
                );
            }

            // Convert data if not already a string
            if (s.data && s.processData && typeof s.data !== "string") {
                s.data = jQuery.param(s.data, s.traditional);
            }

            // Apply prefilters
            inspectPrefiltersOrTransports(prefilters, s, options, jqXHR);

            // If request was aborted inside a prefilter, stop there
            if (state === 2) {
                return jqXHR;
            }

            // We can fire global events as of now if asked to
            fireGlobals = s.global;

            // Watch for a new set of requests
            if (fireGlobals && jQuery.active++ === 0) {
                jQuery.event.trigger("ajaxStart");
            }

            // Uppercase the type
            s.type = s.type.toUpperCase();

            // Determine if request has content
            s.hasContent = !rnoContent.test(s.type);

            // Save the URL in case we're toying with the If-Modified-Since
            // and/or If-None-Match header later on
            cacheURL = s.url;

            // More options handling for requests with no content
            if (!s.hasContent) {

                // If data is available, append data to url
                if (s.data) {
                    cacheURL = ( s.url += ( rquery.test(cacheURL) ? "&" : "?" ) + s.data );
                    // #9682: remove data so that it's not used in an eventual retry
                    delete s.data;
                }

                // Add anti-cache in url if needed
                if (s.cache === false) {
                    s.url = rts.test(cacheURL) ?

                        // If there is already a '_' parameter, set its value
                        cacheURL.replace(rts, "$1_=" + nonce++) :

                        // Otherwise add one to the end
                        cacheURL + ( rquery.test(cacheURL) ? "&" : "?" ) + "_=" + nonce++;
                }
            }

            // Set the If-Modified-Since and/or If-None-Match header, if in ifModified mode.
            if (s.ifModified) {
                if (jQuery.lastModified[cacheURL]) {
                    jqXHR.setRequestHeader("If-Modified-Since", jQuery.lastModified[cacheURL]);
                }
                if (jQuery.etag[cacheURL]) {
                    jqXHR.setRequestHeader("If-None-Match", jQuery.etag[cacheURL]);
                }
            }

            // Set the correct header, if data is being sent
            if (s.data && s.hasContent && s.contentType !== false || options.contentType) {
                jqXHR.setRequestHeader("Content-Type", s.contentType);
            }

            // Set the Accepts header for the server, depending on the dataType
            // jqXHR.setRequestHeader(
            //     "Accept",
            //     s.dataTypes[0] && s.accepts[s.dataTypes[0]] ?
            //         s.accepts[s.dataTypes[0]] + ( s.dataTypes[0] !== "*" ? ", " + allTypes + "; q=0.01" : "" ) :
            //         s.accepts["*"]
            // );

            // Check for headers option
            for (i in s.headers) {
                jqXHR.setRequestHeader(i, s.headers[i]);
            }

            // Allow custom headers/mimetypes and early abort
            if (s.beforeSend && ( s.beforeSend.call(callbackContext, jqXHR, s) === false || state === 2 )) {
                // Abort if not done already and return
                return jqXHR.abort();
            }

            // aborting is no longer a cancellation
            strAbort = "abort";

            // Install callbacks on deferreds
            for (i in {success: 1, error: 1, complete: 1}) {
                jqXHR[i](s[i]);
            }

            // Get transport
            transport = inspectPrefiltersOrTransports(transports, s, options, jqXHR);

            // If no transport, we auto-abort
            if (!transport) {
                done(-1, "No Transport");
            } else {
                jqXHR.readyState = 1;

                // Send global event
                if (fireGlobals) {
                    globalEventContext.trigger("ajaxSend", [jqXHR, s]);
                }
                // Timeout
                if (s.async && s.timeout > 0) {
                    timeoutTimer = setTimeout(function () {
                        jqXHR.abort("timeout");
                    }, s.timeout);
                }

                try {
                    state = 1;
                    transport.send(requestHeaders, done);
                } catch (e) {
                    // Propagate exception as error if not done
                    if (state < 2) {
                        done(-1, e);
                        // Simply rethrow otherwise
                    } else {
                        throw e;
                    }
                }
            }

            // Callback for when everything is done
            function done(status, nativeStatusText, responses, headers) {
                var isSuccess, success, error, response, modified,
                    statusText = nativeStatusText;

                // Called once
                if (state === 2) {
                    return;
                }

                // State is "done" now
                state = 2;

                // Clear timeout if it exists
                if (timeoutTimer) {
                    clearTimeout(timeoutTimer);
                }

                // Dereference transport for early garbage collection
                // (no matter how long the jqXHR object will be used)
                transport = undefined;

                // Cache response headers
                responseHeadersString = headers || "";

                // Set readyState
                jqXHR.readyState = status > 0 ? 4 : 0;

                // Determine if successful
                isSuccess = status >= 200 && status < 300 || status === 304;

                // Get response data
                if (responses) {
                    response = ajaxHandleResponses(s, jqXHR, responses);
                }

                // Convert no matter what (that way responseXXX fields are always set)
                response = ajaxConvert(s, response, jqXHR, isSuccess);

                // If successful, handle type chaining
                if (isSuccess) {

                    // Set the If-Modified-Since and/or If-None-Match header, if in ifModified mode.
                    if (s.ifModified) {
                        modified = jqXHR.getResponseHeader("Last-Modified");
                        if (modified) {
                            jQuery.lastModified[cacheURL] = modified;
                        }
                        modified = jqXHR.getResponseHeader("etag");
                        if (modified) {
                            jQuery.etag[cacheURL] = modified;
                        }
                    }

                    // if no content
                    if (status === 204 || s.type === "HEAD") {
                        statusText = "nocontent";

                        // if not modified
                    } else if (status === 304) {
                        statusText = "notmodified";

                        // If we have data, let's convert it
                    } else {
                        statusText = response.state;
                        success = response.data;
                        error = response.error;
                        isSuccess = !error;
                    }
                } else {
                    // We extract error from statusText
                    // then normalize statusText and status for non-aborts
                    error = statusText;
                    if (status || !statusText) {
                        statusText = "error";
                        if (status < 0) {
                            status = 0;
                        }
                    }
                }

                // Set data for the fake xhr object
                jqXHR.status = status;
                jqXHR.statusText = ( nativeStatusText || statusText ) + "";

                // Success/Error
                if (isSuccess) {
                    deferred.resolveWith(callbackContext, [success, statusText, jqXHR]);
                } else {
                    deferred.rejectWith(callbackContext, [jqXHR, statusText, error]);
                }

                // Status-dependent callbacks
                jqXHR.statusCode(statusCode);
                statusCode = undefined;

                if (fireGlobals) {
                    globalEventContext.trigger(isSuccess ? "ajaxSuccess" : "ajaxError",
                        [jqXHR, s, isSuccess ? success : error]);
                }

                // Complete
                completeDeferred.fireWith(callbackContext, [jqXHR, statusText]);

                if (fireGlobals) {
                    globalEventContext.trigger("ajaxComplete", [jqXHR, s]);
                    // Handle the global AJAX counter
                    if (!( --jQuery.active )) {
                        jQuery.event.trigger("ajaxStop");
                    }
                }
            }

            return jqXHR;
        },

        getJSON: function (url, data, callback) {
            return jQuery.get(url, data, callback, "json");
        },

        getScript: function (url, callback) {
            return jQuery.get(url, undefined, callback, "script");
        }
    });

    jQuery.each(["get", "post"], function (i, method) {
        jQuery[method] = function (url, data, callback, type) {
            // shift arguments if data argument was omitted
            if (jQuery.isFunction(data)) {
                type = type || callback;
                callback = data;
                data = undefined;
            }

            return jQuery.ajax({
                url: url,
                type: method,
                dataType: type,
                data: data,
                success: callback
            });
        };
    });

// Attach a bunch of functions for handling common AJAX events
    jQuery.each(["ajaxStart", "ajaxStop", "ajaxComplete", "ajaxError", "ajaxSuccess", "ajaxSend"], function (i, type) {
        jQuery.fn[type] = function (fn) {
            return this.on(type, fn);
        };
    });


    jQuery._evalUrl = function (url) {
        return jQuery.ajax({
            url: url,
            type: "GET",
            dataType: "script",
            async: false,
            global: false,
            "throws": true
        });
    };


    var r20 = /%20/g,
        rbracket = /\[\]$/,
        rCRLF = /\r?\n/g,
        rsubmitterTypes = /^(?:submit|button|image|reset|file)$/i,
        rsubmittable = /^(?:input|select|textarea|keygen)/i;

    function buildParams(prefix, obj, traditional, add) {
        var name;

        if (jQuery.isArray(obj)) {
            // Serialize array item.
            jQuery.each(obj, function (i, v) {
                if (traditional || rbracket.test(prefix)) {
                    // Treat each array item as a scalar.
                    add(prefix, v);

                } else {
                    // Item is non-scalar (array or object), encode its numeric index.
                    buildParams(prefix + "[" + ( typeof v === "object" ? i : "" ) + "]", v, traditional, add);
                }
            });

        } else if (!traditional && jQuery.type(obj) === "object") {
            // Serialize object item.
            for (name in obj) {
                buildParams(prefix + "[" + name + "]", obj[name], traditional, add);
            }

        } else {
            // Serialize scalar item.
            add(prefix, obj);
        }
    }

// Serialize an array of form elements or a set of
// key/values into a query string
    jQuery.param = function (a, traditional) {
        var prefix,
            s = [],
            add = function (key, value) {
                // If value is a function, invoke it and return its value
                value = jQuery.isFunction(value) ? value() : ( value == null ? "" : value );
                s[s.length] = encodeURIComponent(key) + "=" + encodeURIComponent(value);
            };

        // Set traditional to true for jQuery <= 1.3.2 behavior.
        if (traditional === undefined) {
            traditional = jQuery.ajaxSettings && jQuery.ajaxSettings.traditional;
        }

        // If an array was passed in, assume that it is an array of form elements.
        if (jQuery.isArray(a) || ( a.jquery && !jQuery.isPlainObject(a) )) {
            // Serialize the form elements
            jQuery.each(a, function () {
                add(this.name, this.value);
            });

        } else {
            // If traditional, encode the "old" way (the way 1.3.2 or older
            // did it), otherwise encode params recursively.
            for (prefix in a) {
                buildParams(prefix, a[prefix], traditional, add);
            }
        }

        // Return the resulting serialization
        return s.join("&").replace(r20, "+");
    };

    jQuery.fn.extend({
        serialize: function () {
            return jQuery.param(this.serializeArray());
        },
        serializeArray: function () {
            return this.map(function () {
                // Can add propHook for "elements" to filter or add form elements
                var elements = jQuery.prop(this, "elements");
                return elements ? jQuery.makeArray(elements) : this;
            })
                .filter(function () {
                    var type = this.type;
                    // Use .is(":disabled") so that fieldset[disabled] works
                    return this.name && !jQuery(this).is(":disabled") &&
                        rsubmittable.test(this.nodeName) && !rsubmitterTypes.test(type) &&
                        ( this.checked || !rcheckableType.test(type) );
                })
                .map(function (i, elem) {
                    var val = jQuery(this).val();

                    return val == null ?
                        null :
                        jQuery.isArray(val) ?
                            jQuery.map(val, function (val) {
                                return {name: elem.name, value: val.replace(rCRLF, "\r\n")};
                            }) :
                            {name: elem.name, value: val.replace(rCRLF, "\r\n")};
                }).get();
        }
    });


// Create the request object
// (This is still attached to ajaxSettings for backward compatibility)
    jQuery.ajaxSettings.xhr = window.ActiveXObject !== undefined ?
        // Support: IE6+
        function () {

            // XHR cannot access local files, always use ActiveX for that case
            return !this.isLocal &&

                // Support: IE7-8
                // oldIE XHR does not support non-RFC2616 methods (#13240)
                // See http://msdn.microsoft.com/en-us/library/ie/ms536648(v=vs.85).aspx
                // and http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9
                // Although this check for six methods instead of eight
                // since IE also does not support "trace" and "connect"
                /^(get|post|head|put|delete|options)$/i.test(this.type) &&

                createStandardXHR() || createActiveXHR();
        } :
        // For all other browsers, use the standard XMLHttpRequest object
        createStandardXHR;

    var xhrId = 0,
        xhrCallbacks = {},
        xhrSupported = jQuery.ajaxSettings.xhr();

// Support: IE<10
// Open requests must be manually aborted on unload (#5280)
    if (window.ActiveXObject) {
        jQuery(window).on("unload", function () {
            for (var key in xhrCallbacks) {
                xhrCallbacks[key](undefined, true);
            }
        });
    }

// Determine support properties
    support.cors = !!xhrSupported && ( "withCredentials" in xhrSupported );
    xhrSupported = support.ajax = !!xhrSupported;

// Create transport if the browser can provide an xhr
    if (xhrSupported) {

        jQuery.ajaxTransport(function (options) {
            // Cross domain only allowed if supported through XMLHttpRequest
            if (!options.crossDomain || support.cors) {

                var callback;

                return {
                    send: function (headers, complete) {
                        var i,
                            xhr = options.xhr(),
                            id = ++xhrId;

                        // Open the socket
                        xhr.open(options.type, options.url, options.async, options.username, options.password);

                        // Apply custom fields if provided
                        if (options.xhrFields) {
                            for (i in options.xhrFields) {
                                xhr[i] = options.xhrFields[i];
                            }
                        }

                        // Override mime type if needed
                        if (options.mimeType && xhr.overrideMimeType) {
                            xhr.overrideMimeType(options.mimeType);
                        }

                        // X-Requested-With header
                        // For cross-domain requests, seeing as conditions for a preflight are
                        // akin to a jigsaw puzzle, we simply never set it to be sure.
                        // (it can always be set on a per-request basis or even using ajaxSetup)
                        // For same-domain requests, won't change header if already provided.
                        if (!options.crossDomain && !headers["X-Requested-With"]) {
                            headers["X-Requested-With"] = "XMLHttpRequest";
                        }

                        // Set headers
                        for (i in headers) {
                            // Support: IE<9
                            // IE's ActiveXObject throws a 'Type Mismatch' exception when setting
                            // request header to a null-value.
                            //
                            // To keep consistent with other XHR implementations, cast the value
                            // to string and ignore `undefined`.
                            if (headers[i] !== undefined) {
                                xhr.setRequestHeader(i, headers[i] + "");
                            }
                        }

                        if (xhr.upload && options.progress) {
                            xhr.upload.onprogress = options.progress;
                        }

                        // Do send the request
                        // This may raise an exception which is actually
                        // handled in jQuery.ajax (so no try/catch here)
                        xhr.send(( options.hasContent && (options.body || options.data)) || null);

                        // Listener
                        callback = function (_, isAbort) {
                            var status, statusText, responses;

                            // Was never called and is aborted or complete
                            if (callback && ( isAbort || xhr.readyState === 4 )) {
                                // Clean up
                                delete xhrCallbacks[id];
                                callback = undefined;
                                xhr.onreadystatechange = jQuery.noop;

                                // Abort manually if needed
                                if (isAbort) {
                                    if (xhr.readyState !== 4) {
                                        xhr.abort();
                                    }
                                } else {
                                    responses = {};
                                    status = xhr.status;

                                    // Support: IE<10
                                    // Accessing binary-data responseText throws an exception
                                    // (#11426)
                                    if (typeof xhr.responseText === "string") {
                                        responses.text = xhr.responseText;
                                    }

                                    // Firefox throws an exception when accessing
                                    // statusText for faulty cross-domain requests
                                    try {
                                        statusText = xhr.statusText;
                                    } catch (e) {
                                        // We normalize with Webkit giving an empty statusText
                                        statusText = "";
                                    }

                                    // Filter status for non standard behaviors

                                    // If the request is local and we have data: assume a success
                                    // (success with no data won't get notified, that's the best we
                                    // can do given current implementations)
                                    if (!status && options.isLocal && !options.crossDomain) {
                                        status = responses.text ? 200 : 404;
                                        // IE - #1450: sometimes returns 1223 when it should be 204
                                    } else if (status === 1223) {
                                        status = 204;
                                    }
                                }
                            }

                            // Call complete if needed
                            if (responses) {
                                complete(status, statusText, responses, xhr.getAllResponseHeaders());
                            }
                        };

                        if (!options.async) {
                            // if we're in sync mode we fire the callback
                            callback();
                        } else if (xhr.readyState === 4) {
                            // (IE6 & IE7) if it's in cache and has been
                            // retrieved directly we need to fire the callback
                            setTimeout(callback);
                        } else {
                            // Add to the list of active xhr callbacks
                            xhr.onreadystatechange = xhrCallbacks[id] = callback;
                        }
                    },

                    abort: function () {
                        if (callback) {
                            callback(undefined, true);
                        }
                    }
                };
            }
        });
    }

// Functions to create xhrs
    function createStandardXHR() {
        try {
            return new window.XMLHttpRequest();
        } catch (e) {
        }
    }

    function createActiveXHR() {
        try {
            return new window.ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
        }
    }


// Install script dataType
    jQuery.ajaxSetup({
        accepts: {
            script: "text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"
        },
        contents: {
            script: /(?:java|ecma)script/
        },
        converters: {
            "text script": function (text) {
                jQuery.globalEval(text);
                return text;
            }
        }
    });

// Handle cache's special case and global
    jQuery.ajaxPrefilter("script", function (s) {
        if (s.cache === undefined) {
            s.cache = false;
        }
        if (s.crossDomain) {
            s.type = "GET";
            s.global = false;
        }
    });

// Bind script tag hack transport
    jQuery.ajaxTransport("script", function (s) {

        // This transport only deals with cross domain requests
        if (s.crossDomain) {

            var script,
                head = document.head || jQuery("head")[0] || document.documentElement;

            return {

                send: function (_, callback) {

                    script = document.createElement("script");

                    script.async = true;

                    if (s.scriptCharset) {
                        script.charset = s.scriptCharset;
                    }

                    script.src = s.url;

                    // Attach handlers for all browsers
                    script.onload = script.onreadystatechange = function (_, isAbort) {

                        if (isAbort || !script.readyState || /loaded|complete/.test(script.readyState)) {

                            // Handle memory leak in IE
                            script.onload = script.onreadystatechange = null;

                            // Remove the script
                            if (script.parentNode) {
                                script.parentNode.removeChild(script);
                            }

                            // Dereference the script
                            script = null;

                            // Callback if not abort
                            if (!isAbort) {
                                callback(200, "success");
                            }
                        }
                    };

                    // Circumvent IE6 bugs with base elements (#2709 and #4378) by prepending
                    // Use native DOM manipulation to avoid our domManip AJAX trickery
                    head.insertBefore(script, head.firstChild);
                },

                abort: function () {
                    if (script) {
                        script.onload(undefined, true);
                    }
                }
            };
        }
    });


    var oldCallbacks = [],
        rjsonp = /(=)\?(?=&|$)|\?\?/;

// Default jsonp settings
    jQuery.ajaxSetup({
        jsonp: "callback",
        jsonpCallback: function () {
            var callback = oldCallbacks.pop() || ( jQuery.expando + "_" + ( nonce++ ) );
            this[callback] = true;
            return callback;
        }
    });

// Detect, normalize options and install callbacks for jsonp requests
    jQuery.ajaxPrefilter("json jsonp", function (s, originalSettings, jqXHR) {

        var callbackName, overwritten, responseContainer,
            jsonProp = s.jsonp !== false && ( rjsonp.test(s.url) ?
                        "url" :
                        typeof s.data === "string" && !( s.contentType || "" ).indexOf("application/x-www-form-urlencoded") && rjsonp.test(s.data) && "data"
                );

        // Handle iff the expected data type is "jsonp" or we have a parameter to set
        if (jsonProp || s.dataTypes[0] === "jsonp") {

            // Get callback name, remembering preexisting value associated with it
            callbackName = s.jsonpCallback = jQuery.isFunction(s.jsonpCallback) ?
                s.jsonpCallback() :
                s.jsonpCallback;

            // Insert callback into url or form data
            if (jsonProp) {
                s[jsonProp] = s[jsonProp].replace(rjsonp, "$1" + callbackName);
            } else if (s.jsonp !== false) {
                s.url += ( rquery.test(s.url) ? "&" : "?" ) + s.jsonp + "=" + callbackName;
            }

            // Use data converter to retrieve json after script execution
            s.converters["script json"] = function () {
                if (!responseContainer) {
                    jQuery.error(callbackName + " was not called");
                }
                return responseContainer[0];
            };

            // force json dataType
            s.dataTypes[0] = "json";

            // Install callback
            overwritten = window[callbackName];
            window[callbackName] = function () {
                responseContainer = arguments;
            };

            // Clean-up function (fires after converters)
            jqXHR.always(function () {
                // Restore preexisting value
                window[callbackName] = overwritten;

                // Save back as free
                if (s[callbackName]) {
                    // make sure that re-using the options doesn't screw things around
                    s.jsonpCallback = originalSettings.jsonpCallback;

                    // save the callback name for future use
                    oldCallbacks.push(callbackName);
                }

                // Call if it was a function and we have a response
                if (responseContainer && jQuery.isFunction(overwritten)) {
                    overwritten(responseContainer[0]);
                }

                responseContainer = overwritten = undefined;
            });

            // Delegate to script
            return "script";
        }
    });

// data: string of html
// context (optional): If specified, the fragment will be created in this context, defaults to document
// keepScripts (optional): If true, will include scripts passed in the html string
    jQuery.parseHTML = function (data, context, keepScripts) {
        if (!data || typeof data !== "string") {
            return null;
        }
        if (typeof context === "boolean") {
            keepScripts = context;
            context = false;
        }
        context = context || document;

        var parsed = rsingleTag.exec(data),
            scripts = !keepScripts && [];

        // Single tag
        if (parsed) {
            return [context.createElement(parsed[1])];
        }

        parsed = jQuery.buildFragment([data], context, scripts);

        if (scripts && scripts.length) {
            jQuery(scripts).remove();
        }

        return jQuery.merge([], parsed.childNodes);
    };

    return jQuery;

})();

var stringifyPrimitive = function(v) {
    switch (typeof v) {
        case 'string':
            return v;
        case 'boolean':
            return v ? 'true' : 'false';
        case 'number':
            return isFinite(v) ? v : '';
        default:
            return '';
    }
};

var queryStringify = function(obj, sep, eq, name) {
    sep = sep || '&';
    eq = eq || '=';
    if (obj === null) {
        obj = undefined;
    }
    if (typeof obj === 'object') {
        return Object.keys(obj).map(function(k) {
            var ks = encodeURIComponent(stringifyPrimitive(k)) + eq;
            if (Array.isArray(obj[k])) {
                return obj[k].map(function(v) {
                    return ks + encodeURIComponent(stringifyPrimitive(v));
                }).join(sep);
            } else {
                return ks + encodeURIComponent(stringifyPrimitive(obj[k]));
            }
        }).filter(Boolean).join(sep);

    }
    if (!name) return '';
    return encodeURIComponent(stringifyPrimitive(name)) + eq +
        encodeURIComponent(stringifyPrimitive(obj));
};

var request = function (options, callback) {

    options = $.extend(true, {headers: {}, qs: {}}, options);

    // method
    options.type = options.method;
    delete options.method;

    // progress
    if (options.onProgress) {
        options.progress = options.onProgress;
        delete options.onProgress;
    }

    // qs
    if (options.qs) {
        var qsStr = queryStringify(options.qs);
        if (qsStr) {
            options.url += (options.url.indexOf('?') === -1 ? '?' : '&') + qsStr;
        }
        delete options.qs;
    }

    // json
    if (options.json) {
        options.data = options.body;
        delete options.json;
        delete options.body;
        !options.headers && (options.headers = {});
        options.headers['Content-Type'] = 'application/json';
    }

    // body
    if (options.body) {
        if (!(options.body instanceof Blob || options.body.toString() === '[object File]' || options.body.toString() === '[object Blob]')) {
            options.data = options.body;
            delete options.body;
        }
    }

    // headers
    if (options.headers) {
        var headers = options.headers;
        delete options.headers;
        options.beforeSend = function (xhr) {
            for (var key in headers) {
                if (headers.hasOwnProperty(key) &&
                    key.toLowerCase() !== 'content-length' &&
                    key.toLowerCase() !== 'user-agent' &&
                    key.toLowerCase() !== 'origin' &&
                    key.toLowerCase() !== 'host') {
                    xhr.setRequestHeader(key, headers[key]);
                }
            }
        };
    }

    var getResponse = function (xhr) {
        var headers = {};
        xhr.getAllResponseHeaders().trim().split('\n').forEach(function (item) {
            if (item) {
                var index = item.indexOf(':');
                var key = item.substr(0, index).trim().toLowerCase();
                var val = item.substr(index + 1).trim();
                headers[key] = val;
            }
        });
        return {
            statusCode: xhr.status,
            statusMessage: xhr.statusText,
            headers: headers
        };
    };

    // callback
    options.success = function (data, state, xhr) {
        callback(null, getResponse(xhr), data);
    };
    options.error = function (xhr) {
        if (xhr.responseText) {
            callback(null, getResponse(xhr), xhr.responseText);
        } else {
            callback(xhr.statusText, getResponse(xhr), xhr.responseText);
        }
    };

    options.dataType = 'text';

    // send
    return $.ajax(options);

};

module.exports = request;
