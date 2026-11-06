const themeToggles = document.querySelectorAll('[data-theme-toggle]');

const updateThemeToggles = () => {
    const isDark = document.documentElement.classList.contains('dark');

    themeToggles.forEach((themeToggle) => {
        if (!(themeToggle instanceof HTMLButtonElement)) {
            return;
        }

        const themeLabel = themeToggle.querySelector('[data-theme-label]');
        const themeIcon = themeToggle.querySelector('[data-theme-icon]');

        themeToggle.setAttribute('aria-pressed', String(isDark));

        if (themeLabel) {
            themeLabel.textContent = isDark ? 'Light mode' : 'Dark mode';
        }

        if (themeIcon) {
            themeIcon.textContent = isDark ? '☀' : '☾';
        }
    });
};

updateThemeToggles();

themeToggles.forEach((themeToggle) => {
    if (!(themeToggle instanceof HTMLButtonElement)) {
        return;
    }

    themeToggle.addEventListener('click', () => {
        const isDark = document.documentElement.classList.toggle('dark');

        localStorage.setItem('branch-theme', isDark ? 'dark' : 'light');
        updateThemeToggles();
    });
});

const workTimerDisplay = document.querySelector('[data-work-timer]');
const lunchTimerDisplay = document.querySelector('[data-lunch-timer]');
const workProgress = document.querySelector('[data-work-progress]');

if (workTimerDisplay instanceof HTMLTimeElement) {
    const checkInAt = Date.parse(workTimerDisplay.dataset.checkInAt ?? '');
    const checkOutAt = Date.parse(workTimerDisplay.dataset.checkOutAt ?? '');
    const lunchStartedAt = Date.parse(workTimerDisplay.dataset.lunchStartedAt ?? '');
    const lunchEndedAt = Date.parse(workTimerDisplay.dataset.lunchEndedAt ?? '');
    const workTargetSeconds = Number.parseInt(workProgress?.dataset.workTargetSeconds ?? '', 10);
    const workTargetHours = Number.parseInt(workProgress?.dataset.workTargetHours ?? '', 10);
    const workProgressBar = workProgress?.querySelector('[data-work-progress-bar]');
    const workProgressLabel = workProgress?.querySelector('[data-work-progress-label]');
    const workProgressRemaining = workProgress?.querySelector('[data-work-progress-remaining]');

    const formatDuration = (totalSeconds) => {
        const hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
        const minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
        const seconds = String(totalSeconds % 60).padStart(2, '0');

        return `${hours}:${minutes}:${seconds}`;
    };

    const updateAttendanceTime = () => {
        const currentTime = new Date();

        if (!Number.isFinite(checkInAt)) {
            workTimerDisplay.textContent = 'Not started';
            if (workProgressBar instanceof HTMLDivElement) {
                workProgressBar.setAttribute('aria-valuenow', '0');
                const progressIndicator = workProgressBar.firstElementChild;
                if (progressIndicator instanceof HTMLElement) {
                    progressIndicator.style.width = '0%';
                }
            }
            if (workProgressLabel instanceof HTMLElement) {
                workProgressLabel.textContent = `0% of ${workTargetHours}-hour target`;
            }
            if (workProgressRemaining instanceof HTMLElement) {
                workProgressRemaining.textContent = 'Check in to start progress';
            }

            return;
        }

        const endTime = Number.isFinite(checkOutAt) ? checkOutAt : currentTime.getTime();
        const totalSeconds = Math.max(0, Math.floor((endTime - checkInAt) / 1000));
        let lunchSeconds = 0;

        if (Number.isFinite(lunchStartedAt)) {
            const lunchEndTime = Number.isFinite(lunchEndedAt) ? lunchEndedAt : endTime;

            lunchSeconds = Math.max(0, Math.floor((lunchEndTime - lunchStartedAt) / 1000));
        }

        const elapsedSeconds = Math.max(0, totalSeconds - lunchSeconds);

        workTimerDisplay.textContent = formatDuration(elapsedSeconds);

        if (Number.isFinite(workTargetSeconds) && workTargetSeconds > 0) {
            const progressPercent = Math.min(100, (elapsedSeconds / workTargetSeconds) * 100);
            const remainingSeconds = Math.max(0, workTargetSeconds - elapsedSeconds);

            if (workProgressBar instanceof HTMLDivElement) {
                workProgressBar.setAttribute('aria-valuenow', String(Math.floor(progressPercent)));
                const progressIndicator = workProgressBar.firstElementChild;
                if (progressIndicator instanceof HTMLElement) {
                    progressIndicator.style.width = `${progressPercent}%`;
                }
            }
            if (workProgressLabel instanceof HTMLElement) {
                workProgressLabel.textContent = `${Math.floor(progressPercent)}% of ${workTargetHours}-hour target`;
            }
            if (workProgressRemaining instanceof HTMLElement) {
                workProgressRemaining.textContent = remainingSeconds > 0
                    ? `${formatDuration(remainingSeconds)} remaining`
                    : 'Daily target reached';
            }
        }

        if (lunchTimerDisplay instanceof HTMLTimeElement) {
            if (!Number.isFinite(lunchStartedAt)) {
                lunchTimerDisplay.textContent = 'Not started';
            } else {
                const lunchEndTime = Number.isFinite(lunchEndedAt) ? lunchEndedAt : currentTime.getTime();
                const lunchElapsedSeconds = Math.max(0, Math.floor((lunchEndTime - lunchStartedAt) / 1000));

                lunchTimerDisplay.textContent = formatDuration(lunchElapsedSeconds);
            }
        }
    };

    updateAttendanceTime();
    window.setInterval(updateAttendanceTime, 1000);
}
