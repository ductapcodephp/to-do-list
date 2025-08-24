import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["form", "list"]

    connect() {
        console.log("TaskComponent controller connected")
    }

    toggleForm() {
        this.formTarget.toggleAttribute("hidden")
    }

    submit(event) {
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
                    this.listTarget.innerHTML = data.html
                    form.reset()
                    this.formTarget.hidden = true
                } else {
                    alert(" Có lỗi xảy ra khi thêm task")
                }
            })
            .catch(err => console.error(err))
    }

    deleteSelected() {
        const checkedBoxes = this.listTarget.querySelectorAll("input[type=checkbox]:checked")
        if (checkedBoxes.length === 0) {
            alert(" Bạn chưa chọn task nào để xóa")
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
                    alert(" Không thể xóa task")
                }
            })
    }

    updateTask(event) {
        event.preventDefault()

        const link = event.currentTarget
        const id = link.dataset.taskId

        const form = document.querySelector(`#task-form-${id}`)
        const formData = new FormData(form)

        fetch(`/updateTask/${id}`, {
            method: "PUT",
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        })
            .then(r => r.json())
            .then(json => {
                console.log("Response:", json)
                if (json.success) {
                    alert("Update thành công ✅")
                } else {
                    alert("Có lỗi khi update")
                }
            })
    }

}
