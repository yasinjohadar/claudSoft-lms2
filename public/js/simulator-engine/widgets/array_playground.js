(function () {
    'use strict';

    if (!window.SimulatorEngine) return;

    window.SimulatorEngine.registerWidget('array_playground', function (container, config) {
        const operations = config.operations || ['push', 'pop', 'map', 'filter'];
        const defaultValues = (config.defaultValues || [1, 2, 3]).map(String);
        let arr = defaultValues.slice();
        let output = '';

        function renderArray() {
            visual.innerHTML = arr.map(function (v, i) {
                return '<span class="array-cell" data-index="' + i + '">' + v + '</span>';
            }).join('') || '<span class="text-muted">[]</span>';
        }

        function log(msg) {
            output = msg;
            outEl.textContent = output;
        }

        const visual = document.createElement('div');
        visual.className = 'array-visual';

        const controls = document.createElement('div');
        controls.className = 'array-controls';

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm';
        input.style.maxWidth = '120px';
        input.placeholder = 'قيمة';
        input.dir = 'ltr';

        operations.forEach(function (op) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'simulator-btn simulator-btn-outline';
            btn.textContent = op;
            btn.addEventListener('click', function () {
                try {
                    switch (op) {
                        case 'push':
                            arr.push(input.value || '0');
                            log('push(' + JSON.stringify(input.value || '0') + ') → length ' + arr.length);
                            break;
                        case 'pop':
                            const popped = arr.pop();
                            log('pop() → ' + JSON.stringify(popped));
                            break;
                        case 'shift':
                            const shifted = arr.shift();
                            log('shift() → ' + JSON.stringify(shifted));
                            break;
                        case 'unshift':
                            arr.unshift(input.value || '0');
                            log('unshift(' + JSON.stringify(input.value || '0') + ')');
                            break;
                        case 'reverse':
                            arr.reverse();
                            log('reverse()');
                            break;
                        case 'sort':
                            arr.sort(function (a, b) { return Number(a) - Number(b); });
                            log('sort()');
                            break;
                        case 'map':
                            arr = arr.map(function (v) { return String(Number(v) * 2); });
                            log('map(x => x * 2)');
                            break;
                        case 'filter':
                            arr = arr.filter(function (v) { return Number(v) % 2 === 0; });
                            log('filter(x => x % 2 === 0)');
                            break;
                        case 'reduce':
                            const sum = arr.reduce(function (a, b) { return Number(a) + Number(b); }, 0);
                            log('reduce sum → ' + sum);
                            break;
                        case 'slice':
                            arr = arr.slice(0, Math.max(1, arr.length - 1));
                            log('slice(0, -1)');
                            break;
                        default:
                            log('عملية: ' + op);
                    }
                } catch (e) {
                    log('خطأ: ' + e.message);
                }
                renderArray();
            });
            controls.appendChild(btn);
        });

        const tryBtn = document.createElement('button');
        tryBtn.type = 'button';
        tryBtn.className = 'simulator-btn';
        tryBtn.textContent = 'تجربة';
        tryBtn.addEventListener('click', function () {
            arr = defaultValues.slice();
            renderArray();
            log('تمت إعادة التعيين');
        });

        const outEl = document.createElement('div');
        outEl.className = 'array-output';

        controls.prepend(input);
        controls.appendChild(tryBtn);

        container.appendChild(visual);
        container.appendChild(controls);
        container.appendChild(outEl);

        renderArray();
        log('جاهز — اختر عملية');
    });
})();
