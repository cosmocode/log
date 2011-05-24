addInitEvent(function () {
    if (typeof doku_ajax === 'undefined') {
        return;
    }

    var forms = getElementsByClass('plugin_log', document, 'form');

    if (forms.length === 0) {
        return;
    }

    function handleEvent(form) {
         var loading;
         var ajax = new doku_ajax('plugin_log', form);
         ajax.onCompletion = function () {
             if (this.responseStatus[0] !== 200) {
                 alert(this.response);
                 loading.parentNode.removeChild(loading);
                 return;
             }
             var list = form;
             do {
                 list = list.parentNode;
             } while (list && {'ul': 1, 'ol': 1}[list.tagName.toLowerCase()] !== 1);
             var p = list.parentNode;
             var n = document.createElement('div');
             n.innerHTML = this.response;
             p.replaceChild(n.firstChild, list);
             var new_form = getElementsByClass('plugin_log', p, 'form')[0];
             addEvent(getElementsByClass('button', new_form, 'input')[0],
                      'click', bind(handleEvent, new_form));

         };

         ajax.runAJAX();
         loading = document.createElement('img');
         loading.src = DOKU_BASE+'lib/images/throbber.gif';
         loading.alt = '...';
         loading.className = 'load';
         loading.style.marginBottom = '-5px';
         form.appendChild(loading);
         return false;
     }


    for (var form in forms) {
        addEvent(getElementsByClass('button', forms[form], 'input')[0],
                 'click', bind(handleEvent, forms[form]));
    }
});
