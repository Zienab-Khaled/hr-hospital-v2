@props(['name' => 'signature_data', 'currentImage' => null, 'label' => null])
@php
    $label = $label ?? (app()->getLocale() === 'ar' ? 'ارسم توقيعك أدناه' : 'Draw your signature below');
    $clearLabel = app()->getLocale() === 'ar' ? 'مسح' : 'Clear';
    $wrapperId = 'sig-pad-' . str_replace(['[', ']', '_'], ['-', '', '-'], $name);
@endphp
<div id="{{ $wrapperId }}" class="signature-pad-wrapper" data-input-name="{{ $name }}">
    @if($label)
        <label class="block text-sm font-medium text-slate-700 mb-1">{{ $label }}</label>
    @endif
    @if($currentImage)
        <div class="mb-2 flex items-center gap-3">
            <img src="{{ route('storage.serve', ['path' => $currentImage]) }}" alt="Current signature" class="h-14 object-contain border border-slate-200 rounded p-1 bg-white">
            <span class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'التوقيع الحالي' : 'Current signature' }}</span>
        </div>
        <p class="text-xs text-slate-500 mb-2">{{ app()->getLocale() === 'ar' ? 'ارسم في المربع أدناه لاستبدال التوقيع، أو اتركه فارغًا للإبقاء على الحالي.' : 'Draw in the box below to replace signature, or leave empty to keep current.' }}</p>
    @endif
    <div class="border-2 border-slate-300 rounded-lg bg-white overflow-hidden" style="max-width: 400px;">
        <canvas class="signature-pad-canvas w-full" width="400" height="160" style="touch-action: none; display: block;"></canvas>
    </div>
    <div class="mt-2 flex gap-2">
        <button type="button" class="signature-pad-clear bg-slate-200 text-slate-700 px-3 py-1.5 rounded text-sm hover:bg-slate-300">
            {{ $clearLabel }}
        </button>
    </div>
    <input type="hidden" name="{{ $name }}" class="signature-pad-input" value="">
</div>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
(function() {
    var wrapper = document.getElementById('{{ $wrapperId }}');
    if (!wrapper) return;
    var canvas = wrapper.querySelector('.signature-pad-canvas');
    var input = wrapper.querySelector('.signature-pad-input');
    var clearBtn = wrapper.querySelector('.signature-pad-clear');
    var form = wrapper.closest('form');
    if (!canvas || !input) return;

    var pad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)',
        penColor: 'rgb(0, 0, 0)',
        minWidth: 1,
        maxWidth: 2,
    });

    function resize() {
        var ratio = Math.max(window.devicePixelRatio || 1, 1);
        var w = canvas.offsetWidth;
        var h = canvas.offsetHeight;
        if (w && h) {
            canvas.width = w * ratio;
            canvas.height = h * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            pad.resize();
        }
    }
    window.addEventListener('resize', resize);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', resize);
    } else {
        setTimeout(resize, 50);
    }

    clearBtn.addEventListener('click', function() {
        pad.clear();
        input.value = '';
    });

    if (form) {
        form.addEventListener('submit', function() {
            if (!pad.isEmpty()) {
                input.value = pad.toDataURL('image/png');
            }
        });
    }
})();
</script>
