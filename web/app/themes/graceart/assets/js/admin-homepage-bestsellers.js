(function (wp, config) {
    if (!wp || !config || !wp.data) {
        return;
    }

    let currentDocument = null;
    let selectedIds = [];

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

    function getSelectedIds() {
        const meta = getPostMeta();
        const ids = meta['_graceart_home_bestseller_ids'];

        return Array.isArray(ids) ? ids.map(function (id) { return String(id); }) : [];
    }

    function getProductLabel(id) {
        const product = (config.products || []).find(function (candidate) {
            return String(candidate.value) === String(id);
        });

        return product ? product.label : id;
    }

    function injectStyles(editorDocument) {
        if (editorDocument.getElementById('graceart-homepage-bestsellers-editor-style')) {
            return;
        }

        const style = editorDocument.createElement('style');
        style.id = 'graceart-homepage-bestsellers-editor-style';
        style.textContent = [
            '.graceart-homepage-bestsellers-block { max-width: 650px; margin: 18px auto 0; padding: 16px; border: 1px solid #ddd; background: #fff; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }',
            '.graceart-homepage-bestsellers-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }',
            '.graceart-homepage-bestsellers-title { margin: 0; font-size: 14px; font-weight: 600; color: #1e1e1e; }',
            '.graceart-homepage-bestsellers-count { flex: 0 0 auto; padding: 2px 8px; border-radius: 999px; background: #f0f0f0; color: #50575e; font-size: 12px; }',
            '.graceart-homepage-bestsellers-order { display: flex; flex-direction: column; gap: 6px; margin: 0; padding: 0; list-style: none; }',
            '.graceart-homepage-bestseller-order-item { display: flex; align-items: center; gap: 8px; padding: 6px 10px; border: 1px solid #dcdcde; border-radius: 4px; background: #fff; font-size: 13px; color: #1e1e1e; transition: opacity .12s ease, border-color .12s ease; }',
            '.graceart-homepage-bestseller-order-item.is-dragging { opacity: 0.4; }',
            '.graceart-homepage-bestseller-order-item.is-drag-over { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; }',
            '.graceart-homepage-bestseller-order-handle { flex: 0 0 auto; cursor: grab; color: #757575; font-size: 16px; line-height: 1; user-select: none; touch-action: none; }',
            '.graceart-homepage-bestseller-order-label { flex: 1 1 auto; }',
            '.graceart-homepage-bestseller-order-remove { flex: 0 0 auto; padding: 2px 8px; font-size: 12px; line-height: 1.4; cursor: pointer; color: #b32d2e; background: transparent; border: 1px solid #dcdcde; border-radius: 4px; }',
            '.graceart-homepage-bestsellers-order-empty { margin: 0; color: #757575; font-size: 12px; font-style: italic; }',
            '.graceart-homepage-bestsellers-add { display: flex; gap: 8px; margin-top: 12px; }',
            '.graceart-homepage-bestsellers-add select { flex: 1 1 auto; font-size: 13px; padding: 5px 8px; }',
            '.graceart-homepage-bestsellers-add button { flex: 0 0 auto; font-size: 13px; padding: 5px 12px; cursor: pointer; }',
            '.graceart-homepage-bestsellers-block p.help { margin: 10px 0 0; color: #757575; font-size: 12px; line-height: 1.4; }',
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

    function saveSelectedIds(values) {
        wp.data.dispatch('core/editor').editPost({
            meta: {_graceart_home_bestseller_ids: values.map(function (value) { return parseInt(value, 10) || 0; })},
        });
    }

    function addSelected(block, id) {
        if (!id || selectedIds.indexOf(id) !== -1) {
            return;
        }

        const next = selectedIds.concat([id]);
        selectedIds = next;
        saveSelectedIds(next);
        syncPicker(block);
    }

    function removeSelected(block, index) {
        const next = selectedIds.slice();
        next.splice(index, 1);

        selectedIds = next;
        saveSelectedIds(next);
        syncPicker(block);
    }

    function reorderSelected(block, fromIndex, toIndex) {
        if (fromIndex === toIndex || !selectedIds[fromIndex]) {
            return;
        }

        const next = selectedIds.slice();
        const [moved] = next.splice(fromIndex, 1);
        next.splice(toIndex, 0, moved);

        selectedIds = next;
        saveSelectedIds(next);
        syncPicker(block);
    }

    function createOrderItem(editorDocument, block, id, index) {
        const item = editorDocument.createElement('li');
        const handle = editorDocument.createElement('span');
        const label = editorDocument.createElement('span');
        const removeBtn = editorDocument.createElement('button');

        item.className = 'graceart-homepage-bestseller-order-item';

        handle.className = 'graceart-homepage-bestseller-order-handle';
        handle.textContent = '⠿';
        handle.title = 'Presunúť ťahaním';
        handle.addEventListener('mousedown', function () {
            item.setAttribute('draggable', 'true');
        });
        handle.addEventListener('mouseup', function () {
            item.removeAttribute('draggable');
        });

        label.className = 'graceart-homepage-bestseller-order-label';
        label.textContent = getProductLabel(id);

        removeBtn.type = 'button';
        removeBtn.className = 'graceart-homepage-bestseller-order-remove';
        removeBtn.textContent = '✕';
        removeBtn.title = 'Odstrániť';
        removeBtn.addEventListener('click', function (event) {
            event.preventDefault();
            removeSelected(block, index);
        });

        item.addEventListener('dragstart', function (event) {
            event.dataTransfer.setData('text/plain', String(index));
            event.dataTransfer.effectAllowed = 'move';
            item.classList.add('is-dragging');
        });

        item.addEventListener('dragend', function () {
            item.classList.remove('is-dragging');
            item.removeAttribute('draggable');
        });

        item.addEventListener('dragover', function (event) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            item.classList.add('is-drag-over');
        });

        item.addEventListener('dragleave', function () {
            item.classList.remove('is-drag-over');
        });

        item.addEventListener('drop', function (event) {
            event.preventDefault();
            item.classList.remove('is-drag-over');

            const fromIndex = parseInt(event.dataTransfer.getData('text/plain'), 10);

            if (!isNaN(fromIndex)) {
                reorderSelected(block, fromIndex, index);
            }
        });

        item.appendChild(handle);
        item.appendChild(label);
        item.appendChild(removeBtn);

        return item;
    }

    function syncPicker(block) {
        const count = block.querySelector('[data-graceart-selected-count]');

        if (count) {
            count.textContent = String(selectedIds.length);
        }

        const orderList = block.querySelector('[data-graceart-order-list]');
        const emptyNotice = block.querySelector('[data-graceart-order-empty]');

        if (orderList) {
            while (orderList.firstChild) {
                orderList.removeChild(orderList.firstChild);
            }

            const editorDocument = orderList.ownerDocument;

            selectedIds.forEach(function (id, index) {
                orderList.appendChild(createOrderItem(editorDocument, block, id, index));
            });
        }

        if (emptyNotice) {
            emptyNotice.style.display = selectedIds.length ? 'none' : '';
        }

        const addSelect = block.querySelector('[data-graceart-add-select]');

        if (addSelect) {
            const editorDocument = addSelect.ownerDocument;
            const previousValue = addSelect.value;

            while (addSelect.firstChild) {
                addSelect.removeChild(addSelect.firstChild);
            }

            const available = (config.products || []).filter(function (product) {
                return selectedIds.indexOf(String(product.value)) === -1;
            });

            available.forEach(function (product) {
                const option = editorDocument.createElement('option');
                option.value = String(product.value);
                option.textContent = product.label;
                addSelect.appendChild(option);
            });

            if (available.some(function (product) { return String(product.value) === previousValue; })) {
                addSelect.value = previousValue;
            }

            addSelect.disabled = available.length === 0;
        }
    }

    function createBlock(editorDocument) {
        const block = editorDocument.createElement('div');
        const header = editorDocument.createElement('div');
        const title = editorDocument.createElement('h2');
        const count = editorDocument.createElement('span');
        const orderList = editorDocument.createElement('ul');
        const orderEmpty = editorDocument.createElement('p');
        const addRow = editorDocument.createElement('div');
        const addSelect = editorDocument.createElement('select');
        const addBtn = editorDocument.createElement('button');
        const help = editorDocument.createElement('p');

        block.id = 'graceart-homepage-bestsellers-block';
        block.className = 'graceart-homepage-bestsellers-block';

        header.className = 'graceart-homepage-bestsellers-header';
        title.className = 'graceart-homepage-bestsellers-title';
        title.textContent = 'Najpredávanejšie produkty na domovskej stránke';
        count.className = 'graceart-homepage-bestsellers-count';
        count.setAttribute('data-graceart-selected-count', 'true');
        header.appendChild(title);
        header.appendChild(count);

        orderList.className = 'graceart-homepage-bestsellers-order';
        orderList.setAttribute('data-graceart-order-list', 'true');

        orderEmpty.className = 'graceart-homepage-bestsellers-order-empty';
        orderEmpty.setAttribute('data-graceart-order-empty', 'true');
        orderEmpty.textContent = 'Zatiaľ nie sú vybrané žiadne produkty. Zobrazia sa automaticky najpredávanejšie produkty.';

        addRow.className = 'graceart-homepage-bestsellers-add';
        addSelect.setAttribute('data-graceart-add-select', 'true');
        addBtn.type = 'button';
        addBtn.textContent = '+ Pridať produkt';
        addBtn.addEventListener('click', function (event) {
            event.preventDefault();
            addSelected(block, addSelect.value);
        });

        addRow.appendChild(addSelect);
        addRow.appendChild(addBtn);

        help.className = 'help';
        help.textContent = 'Pridajte, odstráňte alebo potiahnutím zoraďte produkty zobrazené v sekcii "Shop our best-sellers" na domovskej stránke.';

        block.appendChild(header);
        block.appendChild(orderList);
        block.appendChild(orderEmpty);
        block.appendChild(addRow);
        block.appendChild(help);
        syncPicker(block);

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
        selectedIds = getSelectedIds();
        injectStyles(editorDocument);

        let block = editorDocument.getElementById('graceart-homepage-bestsellers-block');

        if (!block) {
            block = createBlock(editorDocument);

            const heroBlock = editorDocument.getElementById('graceart-homepage-hero-block');
            const categoriesBlock = editorDocument.getElementById('graceart-homepage-categories-block');
            const anchor = heroBlock || categoriesBlock || title;

            anchor.insertAdjacentElement('afterend', block);
        }

        syncPicker(block);
    }

    function boot() {
        placeBlock();

        window.setInterval(placeBlock, 500);

        wp.data.subscribe(function () {
            const nextSelectedIds = getSelectedIds();

            if (nextSelectedIds.join(',') === selectedIds.join(',')) {
                return;
            }

            selectedIds = nextSelectedIds;

            if (!currentDocument) {
                return;
            }

            const block = currentDocument.getElementById('graceart-homepage-bestsellers-block');

            if (block) {
                syncPicker(block);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
        return;
    }

    boot();
})(window.wp, window.graceartHomepageBestsellers);
