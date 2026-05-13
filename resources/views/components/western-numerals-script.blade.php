{{-- أرقام لاتينية (0–9) في حقول الأرقام: المتصفح/النظام قد يعرض أرقاماً هندية رغم lang=ar-SA-u-nu-latn --}}
<script>
(function () {
    if (window.__westernNumeralsBound) return;
    window.__westernNumeralsBound = true;

    var EASTERN = /[\u0660-\u0669\u06F0-\u06F9]/;

    function toWestern(s) {
        if (s == null || s === '') return s;
        var out = '';
        for (var i = 0; i < s.length; i++) {
            var ch = s[i];
            var c = ch.charCodeAt(0);
            if (c >= 0x0660 && c <= 0x0669) out += String(c - 0x0660);
            else if (c >= 0x06F0 && c <= 0x06F9) out += String(c - 0x06F0);
            else out += ch;
        }
        return out;
    }

    function shouldNormalize(el) {
        if (!el || el.nodeName !== 'INPUT' || el.disabled || el.readOnly) return false;
        if (el.classList && el.classList.contains('allow-eastern-numerals')) return false;
        var t = (el.getAttribute('type') || 'text').toLowerCase();
        if (t === 'number' || t === 'tel') return true;
        if (t === 'text') {
            var im = (el.getAttribute('inputmode') || '').toLowerCase();
            if (im === 'decimal' || im === 'numeric') return true;
            if (el.getAttribute('data-western-numerals') === '1') return true;
        }
        return false;
    }

    function normalize(el) {
        if (!shouldNormalize(el)) return;
        var v = el.value;
        if (!v || !EASTERN.test(v)) return;
        var nw = toWestern(v);
        if (nw === v) return;
        var start = el.selectionStart;
        var end = el.selectionEnd;
        el.value = nw;
        if (typeof start === 'number' && typeof end === 'number') {
            try { el.setSelectionRange(start, end); } catch (e) {}
        }
        el.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function fixDateTimeInputsBidi() {
        document.querySelectorAll('input[type="date"], input[type="datetime-local"], input[type="time"]').forEach(function (el) {
            el.setAttribute('dir', 'ltr');
            el.setAttribute('lang', 'en');
        });
    }

    function bind() {
        document.body.addEventListener('input', function (e) { normalize(e.target); });
        document.body.addEventListener('blur', function (e) { normalize(e.target); }, true);
        document.body.addEventListener('paste', function (e) {
            if (shouldNormalize(e.target)) setTimeout(function () { normalize(e.target); }, 0);
        });
        document.querySelectorAll('input[type="number"], input[type="tel"]').forEach(normalize);
        document.querySelectorAll('input[type="text"][inputmode="decimal"], input[type="text"][inputmode="numeric"]').forEach(normalize);
        document.querySelectorAll('input[type="text"][data-western-numerals="1"]').forEach(normalize);
        fixDateTimeInputsBidi();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bind);
    else bind();
})();
</script>
