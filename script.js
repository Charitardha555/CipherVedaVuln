document.addEventListener("DOMContentLoaded", function () {
    const bars = document.querySelectorAll(".bar");

    bars.forEach(function (bar, index) {
        bar.style.animationDelay = (index * 80) + "ms";
    });
});