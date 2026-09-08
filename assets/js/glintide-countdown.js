(function () {
	"use strict";

	var DAY_MS = 24 * 60 * 60 * 1000;
	var lunarDigits = ["零", "一", "二", "三", "四", "五", "六", "七", "八", "九"];

	function startOfDay(date) {
		return new Date(date.getFullYear(), date.getMonth(), date.getDate());
	}

	function parseDate(value) {
		var match = String(value || "").match(/^(\d{4})-(\d{2})-(\d{2})$/);
		if (!match) return null;
		return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
	}

	function clamp(value, min, max) {
		return Math.min(max, Math.max(min, value));
	}

	function lunarDay(day) {
		if (day <= 10) return day === 10 ? "初十" : "初" + lunarDigits[day];
		if (day < 20) return "十" + lunarDigits[day - 10];
		if (day === 20) return "二十";
		if (day < 30) return "廿" + lunarDigits[day - 20];
		return "三十";
	}

	function getLunarLabel(date) {
		try {
			var month = new Intl.DateTimeFormat("zh-CN-u-ca-chinese", { month: "long" }).format(date);
			var dayText = new Intl.DateTimeFormat("zh-CN-u-ca-chinese", { day: "numeric" }).format(date);
			var dayMatch = dayText.match(/\d+/);
			return month && dayMatch ? "农历" + month + lunarDay(Number(dayMatch[0])) : "农历日期";
		} catch (error) {
			return "农历日期";
		}
	}

	function updateCalendar(root, now) {
		var monthElement = root.querySelector("[data-calendar-month]");
		var calendarDayElement = root.querySelector("[data-calendar-day]");
		var calendarLunarElement = root.querySelector("[data-calendar-lunar]");
		var calendarWeekdayElement = root.querySelector("[data-calendar-weekday]");

		if (monthElement) monthElement.textContent = new Intl.DateTimeFormat("en-US", { month: "long" }).format(now).toUpperCase();
		if (calendarDayElement) calendarDayElement.textContent = String(now.getDate()).padStart(2, "0");
		if (calendarLunarElement) calendarLunarElement.textContent = getLunarLabel(now);
		if (calendarWeekdayElement) calendarWeekdayElement.textContent = new Intl.DateTimeFormat("en-US", { weekday: "long" }).format(now).toUpperCase();
	}

	function updateCountdown(root, now) {
		var target = parseDate(root.getAttribute("data-target-date"));
		var daysElement = root.querySelector("[data-countdown-days]");
		var dateElement = root.querySelector("[data-countdown-date]");
		var weekdayElement = root.querySelector("[data-countdown-weekday]");

		if (!target || isNaN(target.getTime())) return;

		var days = Math.max(0, Math.round((target.getTime() - now.getTime()) / DAY_MS));
		var yearStart = new Date(target.getFullYear(), 0, 1);
		var total = target.getTime() - yearStart.getTime();
		var elapsed = now.getTime() - yearStart.getTime();
		var progress = total > 0 ? clamp((elapsed / total) * 100, 0, 100) : 0;

		if (daysElement) daysElement.textContent = String(days).padStart(2, "0");
		if (dateElement) dateElement.textContent = new Intl.DateTimeFormat("en-US", { month: "short", day: "2-digit" }).format(now).replace(",", "").toUpperCase();
		if (weekdayElement) weekdayElement.textContent = new Intl.DateTimeFormat("en-US", { weekday: "short" }).format(now).toUpperCase();
		root.style.setProperty("--glintide-countdown-progress", progress.toFixed(2));
	}

	function updateWidget(root) {
		var now = startOfDay(new Date());

		if (root.hasAttribute("data-glintide-countdown")) updateCountdown(root, now);
		if (root.hasAttribute("data-glintide-calendar")) updateCalendar(root, now);
	}

	function boot() {
		var widgets = document.querySelectorAll("[data-glintide-countdown], [data-glintide-calendar]");
		if (!widgets.length) return;

		widgets.forEach(updateWidget);
		window.setInterval(function () {
			widgets.forEach(updateWidget);
		}, 60000);
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", boot);
	} else {
		boot();
	}
}());
