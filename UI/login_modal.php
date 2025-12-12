<div class="modal-overlay" id="loginModal">
    <div class="ticket-modal">
        <span class="close-modal" onclick="toggleModal('loginModal', false)">&times;</span>
        <div id="login-view">
            <h2>LOGIN</h2>
            <form method="POST" id="loginForm">
                <input type="hidden" name="login_form" value="1">
                <div class="input-group">
                    <label>Username</label>
                    <input type="text" name="username" id="loginUser" required 
                           placeholder="Masukkan: user, admin, atau customer">
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" required 
                           placeholder="Masukkan password test">
                </div>
                <button type="submit" class="btn-login-submit">ENTER & LOGIN</button>
            </form>
        </div>
    </div>
</div>