import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["list", "formContainer","status"]

    connect() {
        this.statusTask()
        this.interval = setInterval(() => this.statusTask(), 10000)
    }
    disconnect() {
        clearInterval(this.interval)
    }
   async statusTask(){
        fetch("/tasks/status",{headers:{"Content-Type": "application/json"}})
            .then(res => res.json())
            .then(data => {
                console.log(data)
            })
    }
    openAddForm(event) {
        event.preventDefault()

        fetch("/addTask", {
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
            .then(res => res.text())
            .then(html => {
                this.formContainerTarget.innerHTML = html
                const modal = new bootstrap.Modal(document.getElementById("taskModal"))
                modal.show()
            })
    }

    openEditForm(event) {
        event.preventDefault()
        const id = event.currentTarget.dataset.taskId

        fetch(`/editTask/${id}`, {
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
            .then(res => res.text())
            .then(html => {
                this.formContainerTarget.innerHTML = html
                const modal = new bootstrap.Modal(document.getElementById("taskModal"))
                modal.show()
            })
    }

    saveTask(event) {
        event.preventDefault()

        const form = event.target
        const formData = new FormData(form)

        fetch(form.action, {
            method: form.method,
            body: formData,
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.html) {
                        this.listTarget.insertAdjacentHTML("beforeend", data.html)
                    } else {
                        location.reload()
                    }
                    const modal = bootstrap.Modal.getInstance(document.getElementById("taskModal"))
                    modal.hide()
                } else {
                    alert("Có lỗi xảy ra ❌")
                }
            })
    }

    deleteSelected() {
        const checkedBoxes = this.listTarget.querySelectorAll("input[type=checkbox]:checked")
        if (checkedBoxes.length === 0) {
            alert("Bạn chưa chọn task nào để xóa")
            return
        }

        const ids = Array.from(checkedBoxes).map(cb => cb.dataset.taskId)

        fetch("/deleteTask", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({ ids })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    checkedBoxes.forEach(cb => cb.closest(".task-item").remove())
                } else {
                    alert("Không thể xóa task")
                }
            })
    }
}
