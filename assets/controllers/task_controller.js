import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["title", "list"]

    add(event) {
        event.preventDefault();
        const title = this.titleTarget.value.trim()
        if (!title) return

        fetch("/addTask", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({ title })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const div = document.createElement("div")
                    div.innerHTML = `<input type="checkbox"> <span>${data.task.title}</span>`
                    this.listTarget.appendChild(div)

                    this.titleTarget.value = ""
                }
            })
    }
}
