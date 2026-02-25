<div class="topbar">
    <div class="toggle">
        <button class="toggler-btn" type="button" aria-label="Toggle navigation">
            <i class="bi bi-list-ul" style="font-size: 28px;"></i>
        </button>
    </div>
    <div class="logo d-flex align-items-center">
        <!-- If $firstname isn't defined, fall back to Session or 'Admin' -->
        <span class="username me-2 fw-bold text-primary">
            <?= htmlspecialchars($firstname ?? ucfirst($_SESSION['username'] ?? 'Admin')) ?> (Admin)
        </span>
    </div>
</div>
