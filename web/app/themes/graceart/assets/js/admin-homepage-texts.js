/**
 * Homepage editor: heading above the bestsellers. Mounts a small block under
 * the hero editor and writes to the _graceart_home_bestsellers_title meta.
 */
(function (wp, config) {
    if (!wp || !config || !wp.data) {
        return;
    }

    const META_KEY = '_graceart_home_bestsellers_title';

    function getEditorDocument() {
        const iframe = document.querySelector('iframe[name="editor-canvas"]');

        return iframe && iframe.contentDocument ? iframe.contentDocument : document;
    }

    function isHomepage() {
        const editor = wp.data.select('core/editor');
        const frontPageId = Number(config.frontPageId || 0);
        const postId = editor && editor.getCurrentPostId ? Number(editor.getCurrentPostId()) : 0;

        return !frontPageId || postId === frontPageId;
    }

    function getValue() {
        const editor = wp.data.select('core/editor');
        const meta = editor && editor.getEditedPostAttribute ? editor.getEditedPostAttribute('meta') || {} : {};

        return typeof meta[META_KEY] === 'string' ? meta[META_KEY] : '';
    }

    function setValue(value) {
        const meta = {};
        meta[META_KEY] = value;
        wp.data.dispatch('core/editor').editPost({meta: meta});
    }

    function createBlock(editorDocument) {
        const block = editorDocument.createElement('div');
        const title = editorDocument.createElement('h2');
        const fields = editorDocument.createElement('div');
        const label = editorDocument.createElement('label');
        const input = editorDocument.createElement('input');
        const help = editorDocument.createElement('p');

        block.id = 'graceart-homepage-texts-block';
        block.className = 'graceart-homepage-hero-block';

        title.className = 'graceart-homepage-hero-title';
        title.textContent = 'Nadpis sekcie Najpredávanejšie produkty';

        label.textContent = 'Nadpis';
        label.setAttribute('for', 'graceart-homepage-bestsellers-title');

        input.type = 'text';
        input.id = 'graceart-homepage-bestsellers-title';
        input.placeholder = config.defaultBestsellersTitle || '';
        input.value = getValue();
        input.addEventListener('input', function () {
            setValue(input.value);
        });

        help.className = 'help';
        help.textContent = 'Nechajte prázdne pre predvolený text „' + (config.defaultBestsellersTitle || '') + '“.';

        // Same field styling as the hero slide cards.
        fields.className = 'graceart-homepage-hero-slide-fields';
        fields.appendChild(label);
        fields.appendChild(input);

        block.appendChild(title);
        block.appendChild(fields);
        block.appendChild(help);

        return block;
    }

    function placeBlock() {
        if (!isHomepage()) {
            return;
        }

        const editorDocument = getEditorDocument();
        const heroBlock = editorDocument.getElementById('graceart-homepage-hero-block');

        if (!heroBlock || editorDocument.getElementById('graceart-homepage-texts-block')) {
            return;
        }

        heroBlock.insertAdjacentElement('afterend', createBlock(editorDocument));
    }

    function boot() {
        placeBlock();
        window.setInterval(placeBlock, 500);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
        return;
    }

    boot();
})(window.wp, window.graceartHomepageTexts);
