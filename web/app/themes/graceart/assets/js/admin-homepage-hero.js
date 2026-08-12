(function (wp, config) {
    if (!wp || !config || !wp.data || !wp.media) {
        return;
    }

    const emptySlide = function () {
        return {image_id: 0, title: '', subtitle: '', button_text: '', button_url: ''};
    };

    let currentDocument = null;
    let lastSlidesJSON = '[]';

    function getEditorDocument() {
        const iframe = document.querySelector('iframe[name="editor-canvas"]');

        if (iframe && iframe.contentDocument) {
            return iframe.contentDocument;
        }

        return document;
    }

    function getPostId() {
        const editor = wp.data.select('core/editor');

        return editor && editor.getCurrentPostId ? Number(editor.getCurrentPostId()) : 0;
    }

    function getPostMeta() {
        const editor = wp.data.select('core/editor');

        return editor && editor.getEditedPostAttribute ? editor.getEditedPostAttribute('meta') || {} : {};
    }

    function isHomepage() {
        const frontPageId = Number(config.frontPageId || 0);

        return !frontPageId || getPostId() === frontPageId;
    }

    function getSlides() {
        const meta = getPostMeta();
        const slides = meta['_graceart_home_hero_slides'];

        if (!Array.isArray(slides)) {
            return [];
        }

        return slides.map(function (slide) {
            return Object.assign(emptySlide(), slide || {});
        });
    }

    function commitSlides(slides) {
        lastSlidesJSON = JSON.stringify(slides);
        wp.data.dispatch('core/editor').editPost({meta: {_graceart_home_hero_slides: slides}});
    }

    function updateSlideField(index, field, value) {
        const slides = getSlides();

        if (!slides[index]) {
            return;
        }

        slides[index][field] = value;
        commitSlides(slides);
    }

    function updateSlideImage(index, imageId) {
        const slides = getSlides();

        if (!slides[index]) {
            return;
        }

        slides[index].image_id = imageId;
        commitSlides(slides);
        renderList();
    }

    function removeSlide(index) {
        const slides = getSlides();
        slides.splice(index, 1);
        commitSlides(slides);
        renderList();
    }

    function moveSlide(index, direction) {
        const slides = getSlides();
        const target = index + direction;

        if (target < 0 || target >= slides.length) {
            return;
        }

        const tmp = slides[index];
        slides[index] = slides[target];
        slides[target] = tmp;
        commitSlides(slides);
        renderList();
    }

    function reorderSlide(fromIndex, toIndex) {
        const slides = getSlides();

        if (!slides[fromIndex] || fromIndex === toIndex) {
            return;
        }

        const [moved] = slides.splice(fromIndex, 1);
        slides.splice(toIndex, 0, moved);
        commitSlides(slides);
        renderList();
    }

    function addSlide() {
        const slides = getSlides();
        slides.push(emptySlide());
        commitSlides(slides);
        renderList();
    }

    function resolveImageUrl(imageId, callback) {
        if (!imageId) {
            callback('');
            return;
        }

        const attachment = wp.media.attachment(imageId);
        const cachedUrl = attachment.get('url');

        if (cachedUrl) {
            callback(cachedUrl);
            return;
        }

        attachment.fetch().then(function () {
            callback(attachment.get('url') || '');
        }, function () {
            callback('');
        });
    }

    function openMediaPicker(onSelect) {
        const frame = wp.media({
            title: 'Vybrať obrázok pozadia',
            button: {text: 'Použiť obrázok'},
            multiple: false,
            library: {type: 'image'},
        });

        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();
            onSelect(attachment);
        });

        frame.open();
    }

    function injectStyles(editorDocument) {
        if (editorDocument.getElementById('graceart-homepage-hero-editor-style')) {
            return;
        }

        const style = editorDocument.createElement('style');
        style.id = 'graceart-homepage-hero-editor-style';
        style.textContent = [
            '.graceart-homepage-hero-block { max-width: 650px; margin: 18px auto 0; padding: 16px; border: 1px solid #ddd; background: #fff; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }',
            '.graceart-homepage-hero-header { margin-bottom: 12px; }',
            '.graceart-homepage-hero-title { margin: 0; font-size: 14px; font-weight: 600; color: #1e1e1e; }',
            '.graceart-homepage-hero-list { display: flex; flex-direction: column; gap: 12px; margin: 0 0 12px; padding: 0; list-style: none; }',
            '.graceart-homepage-hero-slide { display: flex; gap: 12px; padding: 12px; border: 1px solid #dcdcde; border-radius: 4px; background: #fff; transition: opacity .12s ease, border-color .12s ease; }',
            '.graceart-homepage-hero-slide.is-dragging { opacity: 0.4; }',
            '.graceart-homepage-hero-slide.is-drag-over { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; }',
            '.graceart-homepage-hero-drag-handle { flex: 0 0 auto; display: flex; align-items: center; justify-content: center; width: 20px; color: #757575; font-size: 18px; line-height: 1; cursor: grab; user-select: none; touch-action: none; }',
            '.graceart-homepage-hero-slide-image { flex: 0 0 96px; }',
            '.graceart-homepage-hero-slide-image-preview { display: block; width: 96px; height: 96px; margin-bottom: 6px; object-fit: cover; border: 1px solid #dcdcde; border-radius: 4px; background: #f0f0f0; }',
            '.graceart-homepage-hero-slide-image-preview.is-empty { display: flex; align-items: center; justify-content: center; color: #757575; font-size: 11px; text-align: center; }',
            '.graceart-homepage-hero-slide-image button { display: block; width: 100%; margin-bottom: 4px; font-size: 11px; padding: 4px 6px; cursor: pointer; }',
            '.graceart-homepage-hero-slide-fields { flex: 1 1 auto; display: flex; flex-direction: column; gap: 6px; min-width: 0; }',
            '.graceart-homepage-hero-slide-fields input { width: 100%; box-sizing: border-box; font-size: 13px; padding: 6px 8px; }',
            '.graceart-homepage-hero-slide-actions { flex: 0 0 auto; display: flex; flex-direction: column; gap: 4px; }',
            '.graceart-homepage-hero-slide-actions button { font-size: 11px; padding: 4px 8px; cursor: pointer; }',
            '.graceart-homepage-hero-add { font-size: 13px; padding: 8px 14px; cursor: pointer; }',
            '.graceart-homepage-hero-block p.help { margin: 10px 0 0; color: #757575; font-size: 12px; line-height: 1.4; }',
        ].join('');
        editorDocument.head.appendChild(style);
    }

    function findTitle(editorDocument) {
        return editorDocument.querySelector('.wp-block-post-title') ||
            editorDocument.querySelector('.editor-post-title__input') ||
            editorDocument.querySelector('[aria-label="Add title"]') ||
            editorDocument.querySelector('[aria-label="Pridať názov"]') ||
            editorDocument.querySelector('[contenteditable="true"]');
    }

    function createTextField(editorDocument, labelText, value, onChange) {
        const label = editorDocument.createElement('label');
        const input = editorDocument.createElement('input');

        input.type = 'text';
        input.placeholder = labelText;
        input.value = value || '';
        input.setAttribute('aria-label', labelText);

        input.addEventListener('input', function () {
            onChange(input.value);
        });

        label.appendChild(input);

        return label;
    }

    function createSlideCard(editorDocument, slide, index, total) {
        const card = editorDocument.createElement('li');
        const dragHandle = editorDocument.createElement('span');
        const imageCol = editorDocument.createElement('div');
        const preview = editorDocument.createElement('img');
        const previewPlaceholder = editorDocument.createElement('span');
        const chooseBtn = editorDocument.createElement('button');
        const removeImgBtn = editorDocument.createElement('button');
        const fields = editorDocument.createElement('div');
        const actions = editorDocument.createElement('div');
        const upBtn = editorDocument.createElement('button');
        const downBtn = editorDocument.createElement('button');
        const removeBtn = editorDocument.createElement('button');

        card.className = 'graceart-homepage-hero-slide';
        card.setAttribute('data-index', String(index));

        dragHandle.className = 'graceart-homepage-hero-drag-handle';
        dragHandle.textContent = '⠿';
        dragHandle.title = 'Presunúť ťahaním';
        dragHandle.addEventListener('mousedown', function () {
            card.setAttribute('draggable', 'true');
        });
        dragHandle.addEventListener('mouseup', function () {
            card.removeAttribute('draggable');
        });

        card.addEventListener('dragstart', function (event) {
            event.dataTransfer.setData('text/plain', String(index));
            event.dataTransfer.effectAllowed = 'move';
            card.classList.add('is-dragging');
        });

        card.addEventListener('dragend', function () {
            card.classList.remove('is-dragging');
            card.removeAttribute('draggable');
        });

        card.addEventListener('dragover', function (event) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            card.classList.add('is-drag-over');
        });

        card.addEventListener('dragleave', function () {
            card.classList.remove('is-drag-over');
        });

        card.addEventListener('drop', function (event) {
            event.preventDefault();
            card.classList.remove('is-drag-over');

            const fromIndex = parseInt(event.dataTransfer.getData('text/plain'), 10);

            if (!isNaN(fromIndex)) {
                reorderSlide(fromIndex, index);
            }
        });

        imageCol.className = 'graceart-homepage-hero-slide-image';
        preview.className = 'graceart-homepage-hero-slide-image-preview';
        preview.alt = '';
        preview.style.display = 'none';

        previewPlaceholder.className = 'graceart-homepage-hero-slide-image-preview is-empty';
        previewPlaceholder.textContent = 'Bez obrázka';

        resolveImageUrl(slide.image_id, function (url) {
            if (url) {
                preview.src = url;
                preview.style.display = '';
                previewPlaceholder.style.display = 'none';
            } else {
                preview.removeAttribute('src');
                preview.style.display = 'none';
                previewPlaceholder.style.display = '';
            }
        });

        chooseBtn.type = 'button';
        chooseBtn.textContent = 'Vybrať obrázok';
        chooseBtn.addEventListener('click', function (event) {
            event.preventDefault();
            openMediaPicker(function (attachment) {
                updateSlideImage(index, attachment.id);
            });
        });

        removeImgBtn.type = 'button';
        removeImgBtn.textContent = 'Odstrániť obrázok';
        removeImgBtn.addEventListener('click', function (event) {
            event.preventDefault();
            updateSlideImage(index, 0);
        });

        imageCol.appendChild(preview);
        imageCol.appendChild(previewPlaceholder);
        imageCol.appendChild(chooseBtn);
        imageCol.appendChild(removeImgBtn);

        fields.className = 'graceart-homepage-hero-slide-fields';
        fields.appendChild(createTextField(editorDocument, 'Nadpis', slide.title, function (value) {
            updateSlideField(index, 'title', value);
        }));
        fields.appendChild(createTextField(editorDocument, 'Podnadpis', slide.subtitle, function (value) {
            updateSlideField(index, 'subtitle', value);
        }));
        fields.appendChild(createTextField(editorDocument, 'Text tlačidla', slide.button_text, function (value) {
            updateSlideField(index, 'button_text', value);
        }));
        fields.appendChild(createTextField(editorDocument, 'Odkaz tlačidla (URL)', slide.button_url, function (value) {
            updateSlideField(index, 'button_url', value);
        }));

        actions.className = 'graceart-homepage-hero-slide-actions';

        upBtn.type = 'button';
        upBtn.textContent = '↑ Hore';
        upBtn.disabled = index === 0;
        upBtn.addEventListener('click', function (event) {
            event.preventDefault();
            moveSlide(index, -1);
        });

        downBtn.type = 'button';
        downBtn.textContent = '↓ Dole';
        downBtn.disabled = index === total - 1;
        downBtn.addEventListener('click', function (event) {
            event.preventDefault();
            moveSlide(index, 1);
        });

        removeBtn.type = 'button';
        removeBtn.textContent = 'Odstrániť';
        removeBtn.addEventListener('click', function (event) {
            event.preventDefault();
            removeSlide(index);
        });

        actions.appendChild(upBtn);
        actions.appendChild(downBtn);
        actions.appendChild(removeBtn);

        card.appendChild(dragHandle);
        card.appendChild(imageCol);
        card.appendChild(fields);
        card.appendChild(actions);

        return card;
    }

    function renderList() {
        if (!currentDocument) {
            return;
        }

        const list = currentDocument.getElementById('graceart-homepage-hero-list');

        if (!list) {
            return;
        }

        const slides = getSlides();

        while (list.firstChild) {
            list.removeChild(list.firstChild);
        }

        slides.forEach(function (slide, index) {
            list.appendChild(createSlideCard(currentDocument, slide, index, slides.length));
        });
    }

    function createBlock(editorDocument) {
        const block = editorDocument.createElement('div');
        const header = editorDocument.createElement('div');
        const title = editorDocument.createElement('h2');
        const list = editorDocument.createElement('ul');
        const addBtn = editorDocument.createElement('button');
        const help = editorDocument.createElement('p');

        block.id = 'graceart-homepage-hero-block';
        block.className = 'graceart-homepage-hero-block';

        header.className = 'graceart-homepage-hero-header';
        title.className = 'graceart-homepage-hero-title';
        title.textContent = 'Hero slider na domovskej stránke';
        header.appendChild(title);

        list.id = 'graceart-homepage-hero-list';
        list.className = 'graceart-homepage-hero-list';

        addBtn.type = 'button';
        addBtn.className = 'graceart-homepage-hero-add';
        addBtn.textContent = '+ Pridať slide';
        addBtn.addEventListener('click', function (event) {
            event.preventDefault();
            addSlide();
        });

        help.className = 'help';
        help.textContent = 'Pridajte, upravte alebo zoraďte slidy hlavného slideru. Ak nie je vytvorený žiadny slide, zobrazí sa predvolený obsah.';

        block.appendChild(header);
        block.appendChild(list);
        block.appendChild(addBtn);
        block.appendChild(help);

        return block;
    }

    function placeBlock() {
        if (!isHomepage()) {
            return;
        }

        const editorDocument = getEditorDocument();
        const title = findTitle(editorDocument);

        if (!title || !editorDocument.body) {
            return;
        }

        currentDocument = editorDocument;
        injectStyles(editorDocument);

        let block = editorDocument.getElementById('graceart-homepage-hero-block');

        if (!block) {
            block = createBlock(editorDocument);

            const categoriesBlock = editorDocument.getElementById('graceart-homepage-categories-block');
            const anchor = categoriesBlock || title;

            anchor.insertAdjacentElement('afterend', block);

            lastSlidesJSON = JSON.stringify(getSlides());
            renderList();
        }
    }

    function boot() {
        placeBlock();

        window.setInterval(placeBlock, 500);

        wp.data.subscribe(function () {
            const nextSlides = getSlides();
            const nextJSON = JSON.stringify(nextSlides);

            if (nextJSON === lastSlidesJSON) {
                return;
            }

            lastSlidesJSON = nextJSON;
            renderList();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
        return;
    }

    boot();
})(window.wp, window.graceartHomepageHero);
