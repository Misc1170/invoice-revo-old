function fetchfunc(action, callback, json, headers = false, method = 'post', before = () => {
}) {
    before();
    const init = {
        method: method,
        mode: 'no-cors',
    };
    if (headers)
        init.headers = new Headers(headers);

    if (json)
        init.body = JSON.stringify((data = json));

    fetch(action, init)
        .then(response => response.json())
        .then(result => callback(result))
}

const cookie = {

    get(cookie_name) {
        const results = document.cookie.match('(^|;) ?' + cookie_name + '=([^;]*)(;|$)');
        return results ? unescape(results[2]) : null;
    },

    set(name, value, exp_y, exp_m, exp_d, path, domain, secure) {
        let cookie_string = name + "=" + escape(value);

        if (exp_y) {
            const expires = new Date(exp_y, exp_m, exp_d);
            cookie_string += "; expires=" + expires.toGMTString();
        }

        if (path)
            cookie_string += "; path=" + escape(path);

        if (domain)
            cookie_string += "; domain=" + escape(domain);

        if (secure)
            cookie_string += "; secure";

        document.cookie = cookie_string;
    },

    delete(cookie_name) {
        const cookie_date = new Date();  // РўРµРєСѓС‰Р°СЏ РґР°С‚Р° Рё РІСЂРµРјСЏ
        cookie_date.setTime(cookie_date.getTime() - 1);
        document.cookie = cookie_name += "=; expires=" + cookie_date.toGMTString();
    }
};

function formExecute(form) {
    const fields = form.elements;
    const data = {};
    for (let i in fields) {
        let field = fields[i];
        if (['SELECT', 'TEXTAREA', 'INPUT'].includes(field.tagName) && field.type !== 'submit') {
            if (['checkbox', 'radio'].includes(field.type)) {
                if (!field.checked)
                    continue;
                else
                    data[field.name] = field.value ? field.value : 1;
            } else
                data[field.name] = field.value;
        }
    }
    return data;
}


function validate(form, fields = []) {

    const data = formExecute(form);
    const submitter = form.querySelector('[type="submit"]');
    const required = [];

    form.querySelectorAll('[data-required]').forEach(input => required.push(input.name));

    fields = fields.length === 0 ? required : fields;

    for (let i in data) {
        if (!data[i] && fields.indexOf(i) !== -1) {
            let input = form.querySelector('[name="' + i + '"]');

            input.focus();
            jrumble(submitter);
            return false;
        }
    }

    return data;
}

function jrumble(element, duration = 800) {
    $(element).jrumble({x: 4, y: 0, rotation: 0, speed: 0}).trigger('startRumble');
    setTimeout(() => $(element).trigger('stopRumble'), duration);
}

function getElemetsAttributes(elements, attributes = []) {
    if (typeof attributes !== 'object')
        attributes = [attributes];

    const data = {};

    if (!(elements instanceof jQuery))
        elements = $(elements);

    elements.each((i, element) => {
        attributes.forEach(attribute => {
            if (data[attribute])
                data[attribute].push(element[attribute]);
            else
                data[attribute] = [element[attribute]]
        });
    });

    return data;
}

//Сохранение инфы о пользователе
window.onload = () => {

    // Initialize the agent at application startup.
    const fpPromise = new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.onload = resolve;
        script.onerror = reject;
        script.async = true;
        script.src = 'https://cdn.jsdelivr.net/npm/@fingerprintjs/fingerprintjs-pro@3/dist/fp.min.js';
        document.head.appendChild(script);

    }).then(() => FingerprintJS.load({token: 'h7x0DxO8VolroOKyIOMk'}));

    // Get the visitor identifier when you need it.
    fpPromise
        .then(fp => fp.get())
        .then( async (result) => {
            let visitorData = {
                path: window.location.href,
                method: 'save_to_BQ',
                ROISTAT_ID: cookie.get('roistat_param_company'),
                SCREEN: screen.width,
                HTTP_REFERER: document.referrer,
                GA_MAIL: cookie.get('_ga'),
                GA_4: cookie.get('_ga_QDLCEDPGSM'),
                YM_UID: cookie.get('_ym_uid'),
                FINGERPRINT_ID: result.visitorId,
                ROISTAT_VID: ''
            };
            $.cookie('FINGERPRINT_ID', visitorData.FINGERPRINT_ID);

            let callbackFunc = response => {
                console.log(response);
            };
            fetchfunc('https://visitourmodel.ru/visitor/index.php', callbackFunc, visitorData);

        });
};