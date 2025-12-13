<?php
// This file is included by index.php when user is admin
// Variables from index.php: $heroMovie, $nowShowing, $userName, $userEmail, etc.
?>

<main id="admin-dashboard" style="padding-top: 100px;">
    <div class="section-header">
        <span>🎬 Admin Control Center</span>
        <h2>MOVIE MANAGEMENT PANEL</h2>
    </div>
    
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        
        <div style="display: flex; align-items: center; margin-bottom: 30px; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #aa2b2b, #d32f2f); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold; margin-right: 25px;">
                <?php echo strtoupper(substr($userName, 0, 1)); ?>
            </div>
            <div style="flex: 1;">
                <h3 style="margin: 0; color: #333; font-size: 28px;">Welcome back, <?php echo htmlspecialchars($userName); ?>! 👋</h3>
                <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">Last login: <?php echo date('d M Y H:i', $_SESSION['login_time'] ?? time()); ?></p>
            </div>
            <div style="text-align: right;">
                <p style="margin: 0; color: #666; font-size: 14px;">Role: <span style="background: linear-gradient(135deg, #aa2b2b, #d32f2f); color: white; padding: 5px 15px; border-radius: 20px; font-size: 14px; font-weight: bold;">ADMINISTRATOR</span></p>
                <a href="?logout=true" style="display: inline-block; margin-top: 10px; color: #aa2b2b; text-decoration: none; font-weight: bold;"><i class="ph ph-sign-out"></i> Logout</a>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 40px;">
            <div style="background: linear-gradient(135deg, #aa2b2b, #d32f2f); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 8px 25px rgba(170, 43, 43, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h4 style="margin: 0 0 10px 0; font-size: 16px; opacity: 0.9;">🎞️ Total Movies</h4>
                        <p style="font-size: 36px; font-weight: bold; margin: 0;"><?php echo count($nowShowing) + ($heroMovie['id'] != 0 ? 1 : 0); ?></p>
                    </div>
                    <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                        <i class="ph ph-film-reel"></i>
                    </div>
                </div>
                <p style="margin: 15px 0 0 0; font-size: 14px; opacity: 0.9;"><?php echo count($nowShowing); ?> showing + 1 featured</p>
            </div>
            
            <div style="background: linear-gradient(135deg, #2196f3, #1976d2); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 8px 25px rgba(33, 150, 243, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h4 style="margin: 0 0 10px 0; font-size: 16px; opacity: 0.9;">📅 Today's Bookings</h4>
                        <p style="font-size: 36px; font-weight: bold; margin: 0;">48</p>
                    </div>
                    <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                        <i class="ph ph-ticket"></i>
                    </div>
                </div>
                <p style="margin: 15px 0 0 0; font-size: 14px; opacity: 0.9;">+12% from yesterday</p>
            </div>
            
            <div style="background: linear-gradient(135deg, #4caf50, #388e3c); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 8px 25px rgba(76, 175, 80, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h4 style="margin: 0 0 10px 0; font-size: 16px; opacity: 0.9;">💰 Revenue Today</h4>
                        <p style="font-size: 36px; font-weight: bold; margin: 0;">Rp 2.4M</p>
                    </div>
                    <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                        <i class="ph ph-currency-circle-dollar"></i>
                    </div>
                </div>
                <p style="margin: 15px 0 0 0; font-size: 14px; opacity: 0.9;">Average ticket: Rp 50,000</p>
            </div>
            
            <div style="background: linear-gradient(135deg, #9c27b0, #7b1fa2); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 8px 25px rgba(156, 39, 176, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h4 style="margin: 0 0 10px 0; font-size: 16px; opacity: 0.9;">👥 Active Users</h4>
                        <p style="font-size: 36px; font-weight: bold; margin: 0;">156</p>
                    </div>
                    <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                        <i class="ph ph-users"></i>
                    </div>
                </div>
                <p style="margin: 15px 0 0 0; font-size: 14px; opacity: 0.9;">3 admins, 153 users</p>
            </div>
        </div>
        
        <div style="margin-bottom: 40px;">
            <h3 style="color: #333; margin-bottom: 20px; font-size: 24px; border-left: 5px solid #aa2b2b; padding-left: 15px;">Quick Actions</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                <button class="btn-primary" onclick="openAdminModal('addMovie')" style="background: #aa2b2b; border: none; padding: 18px; font-size: 16px; display: flex; flex-direction: column; align-items: center; gap: 10px;">
                    <i class="ph ph-plus-circle" style="font-size: 32px;"></i>
                    Add New Movie
                </button>
                <button class="btn-primary" onclick="openAdminModal('editMovies')" style="background: #2196f3; border: none; padding: 18px; font-size: 16px; display: flex; flex-direction: column; align-items: center; gap: 10px;">
                    <i class="ph ph-pencil-simple" style="font-size: 32px;"></i>
                    Edit Movies
                </button>
                <button class="btn-primary" onclick="openAdminModal('viewBookings')" style="background: #4caf50; border: none; padding: 18px; font-size: 16px; display: flex; flex-direction: column; align-items: center; gap: 10px;">
                    <i class="ph ph-list-checks" style="font-size: 32px;"></i>
                    View Bookings
                </button>
                <button class="btn-primary" onclick="openAdminModal('manageUsers')" style="background: #ff9800; border: none; padding: 18px; font-size: 16px; display: flex; flex-direction: column; align-items: center; gap: 10px;">
                    <i class="ph ph-user-circle-gear" style="font-size: 32px;"></i>
                    Manage Users
                </button>
                <button class="btn-primary" onclick="openAdminModal('reports')" style="background: #9c27b0; border: none; padding: 18px; font-size: 16px; display: flex; flex-direction: column; align-items: center; gap: 10px;">
                    <i class="ph ph-chart-bar" style="font-size: 32px;"></i>
                    Generate Reports
                </button>
                <button class="btn-primary" onclick="openAdminModal('settings')" style="background: #607d8b; border: none; padding: 18px; font-size: 16px; display: flex; flex-direction: column; align-items: center; gap: 10px;">
                    <i class="ph ph-gear" style="font-size: 32px;"></i>
                    System Settings
                </button>
            </div>
        </div>
        
        <div style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); margin-bottom: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="color: #333; margin: 0; font-size: 24px; display: flex; align-items: center; gap: 10px;">
                    <i class="ph ph-film-script" style="color: #aa2b2b;"></i>
                    Current Movies Database
                </h3>
                <button onclick="refreshMovies()" style="background: #f5f5f5; border: 1px solid #ddd; padding: 10px 20px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-arrows-clockwise"></i>
                    Refresh
                </button>
            </div>
            
            <div style="background: #f9f9f9; border-radius: 12px; padding: 20px; margin-bottom: 25px; border-left: 5px solid #aa2b2b;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <img src="<?php echo getPoster(safe($heroMovie, 'poster')); ?>" alt="Featured" style="width: 80px; height: 120px; object-fit: cover; border-radius: 8px; border: 3px solid #aa2b2b;" onerror="this.src='https://via.placeholder.com/80x120?text=No+Image'">
                    <div style="flex: 1;">
                        <h4 style="margin: 0 0 5px 0; color: #333; font-size: 20px;"><?php echo safe($heroMovie, 'title'); ?> <span style="background: #aa2b2b; color: white; padding: 3px 10px; border-radius: 12px; font-size: 12px; margin-left: 10px;">FEATURED</span></h4>
                        <p style="margin: 0 0 5px 0; color: #666; font-size: 14px;">Genre: <?php echo safe($heroMovie, 'genre'); ?> | Duration: <?php echo safe($heroMovie, 'duration', '2h 0min'); ?> | Price: Rp <?php echo number_format((int)safe($heroMovie, 'price', 0), 0, ',', '.'); ?></p>
                        <p style="margin: 0; color: #888; font-size: 13px; max-width: 600px;"><?php echo substr(safe($heroMovie, 'synopsis'), 0, 150); ?>...</p>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button onclick="editMovie(<?php echo $heroMovie['id']; ?>)" style="background: #2196f3; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                            <i class="ph ph-pencil-simple"></i> Edit
                        </button>
                        <button onclick="confirmDelete(<?php echo $heroMovie['id']; ?>, '<?php echo addslashes(safe($heroMovie, 'title')); ?>')" style="background: #f44336; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                            <i class="ph ph-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
            
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #2C1E1C, #1F1514); color: white;">
                            <th style="padding: 15px; text-align: left; border-bottom: 2px solid #444; width: 50px;">ID</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 2px solid #444;">Movie Title</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 2px solid #444;">Genre</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 2px solid #444;">Price</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 2px solid #444;">Status</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 2px solid #444; width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($nowShowing)): ?>
                            <tr>
                                <td colspan="6" style="padding: 30px; text-align: center; color: #888;">No other movies found in database.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($nowShowing as $movie): ?>
                            <tr style="border-bottom: 1px solid #eee; transition: background 0.3s;">
                                <td style="padding: 15px; color: #666; font-weight: bold;"><?php echo safe($movie, 'id'); ?></td>
                                <td style="padding: 15px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <img src="<?php echo getPoster(safe($movie, 'poster')); ?>" alt="Poster" style="width: 40px; height: 60px; object-fit: cover; border-radius: 4px;" onerror="this.src='https://via.placeholder.com/40x60?text=No+Image'">
                                        <span style="font-weight: 500; color: #333;"><?php echo safe($movie, 'title'); ?></span>
                                    </div>
                                </td>
                                <td style="padding: 15px; color: #666;"><?php echo safe($movie, 'genre'); ?></td>
                                <td style="padding: 15px; color: #4caf50; font-weight: bold;">Rp <?php echo number_format((int)safe($movie, 'price', 0), 0, ',', '.'); ?></td>
                                <td style="padding: 15px;">
                                    <span style="background: #4caf50; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">ACTIVE</span>
                                </td>
                                <td style="padding: 15px;">
                                    <div style="display: flex; gap: 8px;">
                                        <button onclick="editMovie(<?php echo safe($movie, 'id'); ?>)" style="background: #2196f3; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 5px;">
                                            <i class="ph ph-pencil-simple"></i>
                                        </button>
                                        <button onclick="confirmDelete(<?php echo safe($movie, 'id'); ?>, '<?php echo addslashes(safe($movie, 'title')); ?>')" style="background: #f44336; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 5px;">
                                            <i class="ph ph-trash"></i>
                                        </button>
                                        <button onclick="viewDetails(<?php echo safe($movie, 'id'); ?>)" style="background: #607d8b; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 5px;">
                                            <i class="ph ph-eye"></i>
                                        </button>
                                        <button onclick="featureMovie(<?php echo safe($movie, 'id'); ?>)" style="background: #ff9800; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 5px;">
                                            <i class="ph ph-star"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; margin-bottom: 40px;">
            <div style="background: white; border-radius: 15px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                <h4 style="color: #333; margin: 0 0 20px 0; font-size: 20px; display: flex; align-items: center; gap: 10px;">
                    <i class="ph ph-upload" style="color: #2196f3;"></i>
                    Quick Upload
                </h4>
                <div style="border: 2px dashed #ddd; border-radius: 10px; padding: 30px; text-align: center; background: #f9f9f9;">
                    <i class="ph ph-cloud-arrow-up" style="font-size: 48px; color: #888; margin-bottom: 15px;"></i>
                    <p style="color: #666; margin-bottom: 15px;">Drag & drop movie posters here</p>
                    <input type="file" id="movieUpload" accept="image/*" style="display: none;">
                    <button onclick="document.getElementById('movieUpload').click()" style="background: #2196f3; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: bold;">Browse Files</button>
                    <p style="color: #999; font-size: 12px; margin-top: 10px;">Max size: 5MB | Formats: JPG, PNG, WebP</p>
                </div>
            </div>
            
            <div style="background: white; border-radius: 15px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                <h4 style="color: #333; margin: 0 0 20px 0; font-size: 20px; display: flex; align-items: center; gap: 10px;">
                    <i class="ph ph-chart-line" style="color: #4caf50;"></i>
                    Today's Stats
                </h4>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                        <span style="color: #000000ff;">Movie Views</span>
                        <span style="font-weight: bold; color: #333;">1,245</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                        <span style="color: #000000ff;">Booking Rate</span>
                        <span style="font-weight: bold; color: #4caf50;">12.5%</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                        <span style="color: #000000ff;">Avg. Session</span>
                        <span style="font-weight: bold; color: #2196f3;">4m 32s</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #000000ff;">System Health</span>
                        <span style="font-weight: bold; color: #4caf50;">98% <span style="background: #4caf50; width: 10px; height: 10px; border-radius: 50%; display: inline-block;"></span></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="text-align: center; padding: 20px; background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: center; gap: 20px; margin-bottom: 15px; flex-wrap: wrap;">
                <a href="index.php" style="background: #aa2b2b; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="ph ph-film-slate"></i> Switch to User View
                </a>
                <button onclick="openAdminModal('systemLogs')" style="background: #607d8b; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="ph ph-file-text"></i> View System Logs
                </button>
                <button onclick="openAdminModal('backup')" style="background: #9c27b0; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="ph ph-database"></i> Database Backup
                </button>
            </div>
            <p style="color: #888; font-size: 13px; margin-top: 15px;">
                <i class="ph ph-warning-circle"></i> Admin Panel v2.0 | Last updated: <?php echo date('d M Y H:i:s'); ?>
            </p>
        </div>
    </div>
</main>