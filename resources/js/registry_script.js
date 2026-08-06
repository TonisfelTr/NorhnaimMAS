import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import $ from "jquery";
import "selectize";
import { Modal } from "bootstrap"; // Импортируем модуль для модальных окон

document.addEventListener("DOMContentLoaded", () => {
    const doctorField = $("#doctor-field");
    const patientField = $("#patient-field");
    const datetimeField = document.getElementById("for_datetime");
    const modal = document.getElementById("time-unavailable-modal");
    const form = document.querySelector("form");

    // Инициализация selectize для выбора врача
    doctorField.selectize({
        create: false,
        sortField: "text",
        onChange: () => {
            console.log("Выбран доктор:", doctorField[0].value);
        },
    });

    patientField.selectize({
        create: false,
        sortField: "text",
        onChange: () => {
            console.log("Выбран пациент:", patientField[0].value);
        }
    })

    // Функция для получения текущего значения врача
    const getDoctorId = () => {
        return doctorField[0]?.value || null;
    };

    // Инициализация flatpickr для выбора даты и времени
    const sessionPicker = flatpickr(datetimeField, {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        minuteIncrement: 30,
        time_24hr: true,
        minDate: "today",
        locale: {
            firstDayOfWeek: 1,
        },
        onClose: async (selectedDates, dateStr) => {
            const doctorId = getDoctorId();

            if (!doctorId) {
                console.warn("Доктор не выбран.");
                return;
            }

            // Проверка на занятость времени
            const isAvailable = await checkAvailability(doctorId, dateStr);

            if (!isAvailable) {
                showModal(); // Показываем модальное окно
                sessionPicker.clear(); // Сбрасываем выбранное значение
            }
        },
    });

    // Функция проверки доступности времени
    const checkAvailability = async (doctorId, datetime) => {
        try {
            const response = await fetch(`/admin/dictionary/check-availability?doctor_id=${doctorId}&for_datetime=${datetime}`);
            if (!response.ok) {
                throw new Error("Ошибка сети");
            }

            const data = await response.json();
            return data.available;
        } catch (error) {
            console.error("Ошибка проверки доступности времени:", error);
            return true; // В случае ошибки считаем доступным
        }
    };

    // Показываем модальное окно
    const showModal = () => {
        const modalInstance = new Modal(modal); // Используем модуль Bootstrap
        modalInstance.show();
    };

    // Обработчик отправки формы
    form.addEventListener("submit", (event) => {
        if (!datetimeField.value) {
            event.preventDefault();
            console.warn("Поле for_datetime не заполнено.");
        }
    });
});
