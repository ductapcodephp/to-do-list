import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["title", "description", "startDate", "endDate", "list", "addForm"]

    connect() {
        console.log(this.element)
    }
    toggleAdd(event) {
        event.preventDefault()
        fetch('/addTask', {
            method: "GET",
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
            .then(res => res.text())
            .then(html => {
                document.getElementById('form-container').innerHTML = html
                if (this.hasAddFormTarget) {
                    this.addFormTarget.style.display = "block"
                }
            })
    }

    addTask(event) {
        event.preventDefault()
        const form = event.target
        const formData = new FormData(form)

        fetch("/addTask", {
            method: "POST",
            body: formData,
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    console.log(data.html)
                    this.listTarget.innerHTML = data.html
                    form.reset()
                    if (this.hasAddFormTarget) {
                        this.addFormTarget.style.display = "none"
                    }
                } else {
                    alert("Có lỗi xảy ra")
                }
            })
            .catch(err => console.error(err))
    }

    deleteSelected(event) {
        event.preventDefault()
        const checkedBoxes = this.listTarget.querySelectorAll('input[type="checkbox"]:checked')
        if(checkedBoxes.length === 0) {
            alert('Chưa chọn task nào để xóa')
            return
        }

        const ids = Array.from(checkedBoxes).map(cb => cb.dataset.taskIdValue)

        fetch("/deleteTask", {
            method:"POST",
            headers:{
                "Content-Type": "application/json",
                "X-Requested-With":"XMLHttpRequest"
            },
            body: JSON.stringify({ids})
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    checkedBoxes.forEach(cb => cb.parentElement.remove())
                }
            })
    }
}
