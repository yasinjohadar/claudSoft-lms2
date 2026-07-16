import { EditorView, keymap, lineNumbers, highlightActiveLine, highlightActiveLineGutter } from '@codemirror/view';
import { EditorState, Prec } from '@codemirror/state';
import { defaultKeymap, history, historyKeymap, indentWithTab } from '@codemirror/commands';
import { bracketMatching, foldGutter, indentOnInput } from '@codemirror/language';
import {
    autocompletion,
    completionKeymap,
    closeBrackets,
    closeBracketsKeymap,
    snippetCompletion,
    nextSnippetField,
    prevSnippetField,
    clearSnippet,
} from '@codemirror/autocomplete';
import { html, htmlCompletionSource, autoCloseTags } from '@codemirror/lang-html';
import { css, cssCompletionSource } from '@codemirror/lang-css';
import { javascript } from '@codemirror/lang-javascript';
import { python } from '@codemirror/lang-python';
import { oneDark } from '@codemirror/theme-one-dark';

/** Common HTML tags → insert full markup without requiring a leading "<".
 *  CodeMirror snippets use ${} placeholders (not VS Code's $0).
 */
const HTML_TAG_SNIPPETS = [
    ['html', '<html>\n\t${}\n</html>'],
    ['head', '<head>\n\t${}\n</head>'],
    ['body', '<body>\n\t${}\n</body>'],
    ['title', '<title>${}</title>'],
    ['meta', '<meta charset="${}">'],
    ['link', '<link rel="stylesheet" href="${}">'],
    ['script', '<script>\n\t${}\n</script>'],
    ['style', '<style>\n\t${}\n</style>'],
    ['div', '<div>${}</div>'],
    ['span', '<span>${}</span>'],
    ['p', '<p>${}</p>'],
    ['a', '<a href="${}"></a>'],
    ['img', '<img src="${}" alt="">'],
    ['ul', '<ul>\n\t<li>${}</li>\n</ul>'],
    ['ol', '<ol>\n\t<li>${}</li>\n</ol>'],
    ['li', '<li>${}</li>'],
    ['table', '<table>\n\t<tr>\n\t\t<td>${}</td>\n\t</tr>\n</table>'],
    ['tr', '<tr>${}</tr>'],
    ['td', '<td>${}</td>'],
    ['th', '<th>${}</th>'],
    ['form', '<form action="${}">\n\t\n</form>'],
    ['input', '<input type="${}">'],
    ['button', '<button type="button">${}</button>'],
    ['label', '<label>${}</label>'],
    ['textarea', '<textarea>${}</textarea>'],
    ['select', '<select>\n\t<option>${}</option>\n</select>'],
    ['option', '<option>${}</option>'],
    ['h1', '<h1>${}</h1>'],
    ['h2', '<h2>${}</h2>'],
    ['h3', '<h3>${}</h3>'],
    ['h4', '<h4>${}</h4>'],
    ['h5', '<h5>${}</h5>'],
    ['h6', '<h6>${}</h6>'],
    ['section', '<section>${}</section>'],
    ['article', '<article>${}</article>'],
    ['header', '<header>${}</header>'],
    ['footer', '<footer>${}</footer>'],
    ['nav', '<nav>${}</nav>'],
    ['main', '<main>${}</main>'],
    ['aside', '<aside>${}</aside>'],
    ['br', '<br>'],
    ['hr', '<hr>'],
    ['strong', '<strong>${}</strong>'],
    ['em', '<em>${}</em>'],
    ['code', '<code>${}</code>'],
    ['pre', '<pre>${}</pre>'],
    ['iframe', '<iframe src="${}"></iframe>'],
    ['video', '<video src="${}" controls></video>'],
    ['audio', '<audio src="${}" controls></audio>'],
];

const htmlBareTagOptions = HTML_TAG_SNIPPETS.map(([label, template]) =>
    snippetCompletion(template, {
        label,
        type: 'type',
        detail: 'وسم HTML',
        boost: 99,
    })
);

/**
 * Suggest full tags while typing bare names (div → <div></div>),
 * without requiring the user to type "<" first.
 */
function bareHtmlTagCompletion(context) {
    const word = context.matchBefore(/[A-Za-z!][\w!-]*/);
    if (!word || (word.from === word.to && !context.explicit)) return null;

    const prev = context.state.sliceDoc(Math.max(0, word.from - 2), word.from);
    if (prev.endsWith('<') || prev.endsWith('</')) return null;

    // Inside an open tag → let built-in attribute completion handle it.
    const line = context.state.doc.lineAt(context.pos);
    const beforeOnLine = line.text.slice(0, context.pos - line.from);
    const lastLt = beforeOnLine.lastIndexOf('<');
    const lastGt = beforeOnLine.lastIndexOf('>');
    if (lastLt > lastGt) return null;

    const typed = word.text.toLowerCase();
    const options = context.explicit
        ? htmlBareTagOptions
        : htmlBareTagOptions.filter((opt) => opt.label.toLowerCase().startsWith(typed));

    if (!options.length) return null;

    return {
        from: word.from,
        options,
        validFor: /^[\w!-]*$/,
    };
}

function languageExtensions(langId) {
    const id = String(langId || '').toLowerCase();
    if (id === 'html' || id === 'xml') {
        return {
            langs: [html(), autoCloseTags],
            completionSources: [bareHtmlTagCompletion, htmlCompletionSource],
        };
    }
    if (id === 'css') {
        return {
            langs: [css()],
            completionSources: [cssCompletionSource],
        };
    }
    if (id === 'javascript' || id === 'js' || id === 'typescript' || id === 'ts') {
        return {
            langs: [javascript({ typescript: id === 'typescript' || id === 'ts' })],
            completionSources: [],
        };
    }
    if (id === 'python' || id === 'py') {
        return { langs: [python()], completionSources: [] };
    }
    return { langs: [], completionSources: [] };
}

export function createCodeMirrorEditor(parent, options = {}) {
    const doc = options.doc || '';
    const { langs, completionSources } = languageExtensions(options.language);
    const onChange = typeof options.onChange === 'function' ? options.onChange : null;

    const autocompleteConfig = {
        activateOnTyping: true,
        activateOnTypingDelay: 40,
        maxRenderedOptions: 60,
        defaultKeymap: true,
        icons: true,
    };
    if (completionSources.length) {
        autocompleteConfig.override = completionSources;
    }

    const extensions = [
        lineNumbers(),
        highlightActiveLine(),
        highlightActiveLineGutter(),
        history(),
        foldGutter(),
        indentOnInput(),
        bracketMatching(),
        closeBrackets(),
        autocompletion(autocompleteConfig),
        oneDark,
        Prec.highest(keymap.of([
            { key: 'Tab', run: nextSnippetField, shift: prevSnippetField },
            { key: 'Escape', run: clearSnippet },
        ])),
        keymap.of([
            indentWithTab,
            ...closeBracketsKeymap,
            ...completionKeymap,
            ...defaultKeymap,
            ...historyKeymap,
        ]),
        EditorView.lineWrapping,
        EditorView.updateListener.of((update) => {
            if (update.docChanged && onChange) onChange();
        }),
        EditorView.theme({
            '&': {
                height: '100%',
                backgroundColor: '#1e1e1e',
            },
            '.cm-scroller': {
                fontFamily: "Consolas, 'Cascadia Code', Monaco, 'Courier New', monospace",
                fontSize: '14px',
                lineHeight: '1.55',
            },
            '.cm-content': { caretColor: '#aeafad' },
            '&.cm-focused': { outline: 'none' },
            '.cm-gutters': {
                backgroundColor: '#1e1e1e',
                color: '#858585',
                border: 'none',
            },
            '.cm-activeLineGutter': { backgroundColor: '#2a2a2a' },
            '.cm-activeLine': { backgroundColor: '#2a2a2a66' },
            '.cm-tooltip-autocomplete': {
                border: '1px solid #3e4451',
                backgroundColor: '#21252b',
                color: '#abb2bf',
            },
            '.cm-tooltip-autocomplete ul li[aria-selected]': {
                backgroundColor: '#2c313a',
                color: '#ffffff',
            },
        }),
        ...langs,
    ];

    const view = new EditorView({
        state: EditorState.create({ doc, extensions }),
        parent,
    });

    return {
        getValue: () => view.state.doc.toString(),
        setValue: (value) => {
            view.dispatch({
                changes: { from: 0, to: view.state.doc.length, insert: value || '' },
            });
        },
        focus: () => view.focus(),
        layout: () => view.requestMeasure(),
        destroy: () => view.destroy(),
    };
}

window.__challengeCreateCodeMirror = createCodeMirrorEditor;
window.dispatchEvent(new CustomEvent('challenge-codemirror-ready'));
