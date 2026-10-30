/**
 * Development stand-in for the Packeta pickup point widget. Loaded instead of
 * the real library when graceartPacketaFakeWidgetEnabled() is true, so the
 * checkout can be tested without a Packeta API key. It opens a small modal
 * with a few made-up points of the country the checkout asks for and hands
 * the picked one to the callback in the shape of the real widget.
 */
(function () {
    'use strict';

    var POINTS = {
        sk: [
            { id: '90001', name: 'Z-BOX Bratislava, Obchodná 1', place: 'Z-BOX', street: 'Obchodná 1', city: 'Bratislava', zip: '811 06', pickupPointType: 'external' },
            { id: '90002', name: 'Pošta Žilina 1', place: 'Pošta', street: 'Nám. A. Hlinku 5', city: 'Žilina', zip: '010 01', pickupPointType: 'internal' },
            { id: '90003', name: 'Z-BOX Košice, Hlavná 20', place: 'Z-BOX', street: 'Hlavná 20', city: 'Košice', zip: '040 01', pickupPointType: 'external' }
        ],
        cz: [
            { id: '90101', name: 'Z-BOX Praha, Vodičkova 10', place: 'Z-BOX', street: 'Vodičkova 10', city: 'Praha', zip: '110 00', pickupPointType: 'external' },
            { id: '90102', name: 'Výdejní místo Brno', place: 'Výdejní místo', street: 'Masarykova 3', city: 'Brno', zip: '602 00', pickupPointType: 'internal' }
        ]
    };

    function close(overlay) {
        if (overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
        }
    }

    function pick(apiKey, callback, options) {
        var country = String((options && options.country) || 'sk').toLowerCase();
        var points = POINTS[country] || POINTS.sk;
        var overlay = document.createElement('div');
        var box = document.createElement('div');
        var title = document.createElement('p');
        var cancel = document.createElement('button');

        overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;padding:16px';
        box.style.cssText = 'background:#fff;color:#000;max-width:420px;width:100%;padding:20px;border-radius:8px;font:15px/1.4 sans-serif';
        title.textContent = 'FALOŠNÝ Packeta widget (' + country.toUpperCase() + ') – iba na testovanie';
        title.style.cssText = 'margin:0 0 12px;font-weight:bold;color:#b00020';
        box.appendChild(title);

        points.forEach(function (point) {
            var button = document.createElement('button');

            button.type = 'button';
            button.textContent = point.name + ' – ' + point.street + ', ' + point.zip + ' ' + point.city;
            button.style.cssText = 'display:block;width:100%;text-align:left;margin:0 0 8px;padding:10px;border:1px solid #ccc;background:#f8f8f8;cursor:pointer;color:#000';
            button.addEventListener('click', function () {
                close(overlay);
                callback(Object.assign({ country: country, url: 'https://www.zasilkovna.cz/pobocky/' + point.id }, point));
            });
            box.appendChild(button);
        });

        cancel.type = 'button';
        cancel.textContent = 'Zrušiť';
        cancel.style.cssText = 'margin-top:4px;padding:8px 14px;cursor:pointer;color:#000';
        cancel.addEventListener('click', function () {
            close(overlay);
            callback(null);
        });
        box.appendChild(cancel);

        overlay.appendChild(box);
        document.body.appendChild(overlay);
    }

    window.Packeta = window.Packeta || {};
    window.Packeta.Widget = { pick: pick };
})();
