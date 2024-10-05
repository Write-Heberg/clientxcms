import Sortable from 'sortablejs';
class Sort extends HTMLUListElement {

    connectedCallback() {
        this.saveButton = this.dataset.button;
        this.saveButton = document.querySelector(this.saveButton);
        this.saveButton.addEventListener('click', this.save.bind(this));
        this.saveUrl = this.dataset.url;
        this.init()
    }

    init() {
        this.sortable = Sortable.create(this, {
            animation: 150,
            group: 'item'
        });
    }

    serialize(sortable) {
        const serialize = [].slice.call(sortable.children).filter((child) => {
            return child instanceof HTMLElement;
        }).map(function (child) {
            return child.id;
        });
        return serialize;
    }

    save() {
        const data = new FormData()
        data.append('items', this.serialize(this.sortable.el))

        this.saveButton.disabled = true;
        console.log(this.serialize(this.sortable.el))
        fetch(this.saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                items: this.serialize(this.sortable.el)
            }),
        })
        .then(response => response.json())
        .then(data => {
            window.location.reload();
        })
        .catch((error) => {
            console.error('Error:', error);
        }).finally(() => {
            this.saveButton.disabled = false;
        });
    }
}
customElements.define("sort-list", Sort, { extends: 'ul' })
