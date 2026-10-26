<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\CustomRole;
use App\Models\Platform;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\VideoProject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $team = Team::create(['name' => 'TWC Team']);

        // -- Create Custom Roles with permissions (boss creates these) --
        $managerRole = CustomRole::create([
            'name' => 'Manager', 'slug' => 'manager', 'color' => 'warning',
            'permissions' => ['manage_tasks', 'view_reports', 'manage_channels'],
        ]);

        $scriptRole = CustomRole::create([
            'name' => 'Script Writer', 'slug' => 'script_writer', 'color' => 'info',
            'permissions' => ['write_script', 'assign_to_video'],
        ]);

        $videoRole = CustomRole::create([
            'name' => 'Video Maker', 'slug' => 'video_maker', 'color' => 'success',
            'permissions' => ['make_video'],
        ]);

        $ytRole = CustomRole::create([
            'name' => 'YouTube Head', 'slug' => 'youtube_head', 'color' => 'danger',
            'permissions' => ['upload_youtube'],
        ]);

        $fbRole = CustomRole::create([
            'name' => 'Facebook Head', 'slug' => 'facebook_head', 'color' => 'primary',
            'permissions' => ['upload_facebook'],
        ]);

        $igRole = CustomRole::create([
            'name' => 'Instagram Head', 'slug' => 'instagram_head', 'color' => 'success',
            'permissions' => ['upload_instagram'],
        ]);

        $ttRole = CustomRole::create([
            'name' => 'TikTok Head', 'slug' => 'tiktok_head', 'color' => 'gray',
            'permissions' => ['upload_tiktok'],
        ]);

        // -- Users --
        $owner = User::create([
            'name' => 'Boss', 'email' => 'boss@twc.com',
            'password' => Hash::make('password'), 'role' => 'owner', 'team_id' => $team->id,
        ]);

        $manager = User::create([
            'name' => 'Bilal (Manager)', 'email' => 'manager@twc.com',
            'password' => Hash::make('password'), 'role' => 'member',
            'team_id' => $team->id, 'custom_role_id' => $managerRole->id,
        ]);

        $sw = User::create([
            'name' => 'Ahmed (Script Writer)', 'email' => 'ahmed@twc.com',
            'password' => Hash::make('password'), 'role' => 'member',
            'team_id' => $team->id, 'custom_role_id' => $scriptRole->id,
        ]);

        $vm = User::create([
            'name' => 'Sara (Video Maker)', 'email' => 'sara@twc.com',
            'password' => Hash::make('password'), 'role' => 'member',
            'team_id' => $team->id, 'custom_role_id' => $videoRole->id,
        ]);

        $yt = User::create([
            'name' => 'Ali (YouTube)', 'email' => 'ali@twc.com',
            'password' => Hash::make('password'), 'role' => 'member',
            'team_id' => $team->id, 'custom_role_id' => $ytRole->id,
        ]);

        $fb = User::create([
            'name' => 'Fatima (Facebook)', 'email' => 'fatima@twc.com',
            'password' => Hash::make('password'), 'role' => 'member',
            'team_id' => $team->id, 'custom_role_id' => $fbRole->id,
        ]);

        $ig = User::create([
            'name' => 'Hassan (Instagram)', 'email' => 'hassan@twc.com',
            'password' => Hash::make('password'), 'role' => 'member',
            'team_id' => $team->id, 'custom_role_id' => $igRole->id,
        ]);

        $tt = User::create([
            'name' => 'Zara (TikTok)', 'email' => 'zara@twc.com',
            'password' => Hash::make('password'), 'role' => 'member',
            'team_id' => $team->id, 'custom_role_id' => $ttRole->id,
        ]);

        // Attach all to team
        foreach ([$owner,$manager,$sw,$vm,$yt,$fb,$ig,$tt] as $u) {
            $team->members()->attach($u->id, ['role' => $u->role ?? 'member']);
        }

        // -- Platforms --
        Platform::insert([
            ['name'=>'YouTube','slug'=>'youtube','color'=>'danger','sort_order'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Facebook','slug'=>'facebook','color'=>'primary','sort_order'=>2,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Instagram','slug'=>'instagram','color'=>'success','sort_order'=>3,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['name'=>'TikTok','slug'=>'tiktok','color'=>'gray','sort_order'=>4,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()],
        ]);

        // -- Channels --
        $car = Channel::create([
            'name'=>'Car Fusion','slug'=>'car-fusion','niche'=>'Cars','language'=>'en',
            'team_id'=>$team->id,'target_platforms'=>['youtube','facebook','instagram','tiktok'],
        ]);
        $tech = Channel::create([
            'name'=>'AI Tools Daily','slug'=>'ai-tools-daily','niche'=>'Tech','language'=>'en',
            'team_id'=>$team->id,'target_platforms'=>['youtube','tiktok'],
        ]);
        $facts = Channel::create([
            'name'=>'Facts Universe','slug'=>'facts-universe','niche'=>'Facts','language'=>'en',
            'team_id'=>$team->id,'target_platforms'=>['youtube','facebook','instagram','tiktok'],
        ]);

        // -- Sample Projects + Tasks --
        $p1 = VideoProject::create(['title'=>'Top 5 Supercars 2025','slug'=>'supercars-2025','channel_id'=>$car->id,'status'=>'idea']);
        Task::create(['project_id'=>$p1->id,'channel_id'=>$car->id,'type'=>'script','status'=>'done','assigned_to'=>$sw->id,'assigned_by'=>$manager->id,'deadline'=>now()->subDays(2),'completed_at'=>now()->subDay(),'script_content'=>'<p><b>Hook:</b> These 5 supercars will blow your mind!</p><p>5. McLaren W1<br>4. Ferrari F80<br>3. Lamborghini Revuelto<br>2. Bugatti Tourbillon<br>1. Pagani Utopia</p>']);
        Task::create(['project_id'=>$p1->id,'channel_id'=>$car->id,'type'=>'video','status'=>'in_progress','assigned_to'=>$vm->id,'assigned_by'=>$sw->id,'deadline'=>today()->endOfDay(),'started_at'=>now()->subHours(3)]);

        $p2 = VideoProject::create(['title'=>'AI Tool Replaces Photoshop','slug'=>'ai-photoshop','channel_id'=>$tech->id,'status'=>'idea']);
        Task::create(['project_id'=>$p2->id,'channel_id'=>$tech->id,'type'=>'script','status'=>'in_progress','assigned_to'=>$sw->id,'assigned_by'=>$manager->id,'deadline'=>today()->endOfDay(),'started_at'=>now()->subHours(1)]);

        $p3 = VideoProject::create(['title'=>'5 Facts That Shock You','slug'=>'5-facts','channel_id'=>$facts->id,'status'=>'idea']);
        Task::create(['project_id'=>$p3->id,'channel_id'=>$facts->id,'type'=>'script','status'=>'done','assigned_to'=>$sw->id,'assigned_by'=>$manager->id,'deadline'=>now()->subDays(5),'completed_at'=>now()->subDays(4),'script_content'=>'<p>5 facts that will change how you see the world...</p>']);
        Task::create(['project_id'=>$p3->id,'channel_id'=>$facts->id,'type'=>'video','status'=>'done','assigned_to'=>$vm->id,'assigned_by'=>$sw->id,'deadline'=>now()->subDays(3),'completed_at'=>now()->subDays(2),'video_drive_url'=>'https://drive.google.com/file/d/abc123/view']);
        Task::create(['project_id'=>$p3->id,'channel_id'=>$facts->id,'type'=>'youtube','status'=>'pending','assigned_to'=>$yt->id,'assigned_by'=>$sw->id,'deadline'=>today()->endOfDay(),'video_drive_url'=>'https://drive.google.com/file/d/abc123/view']);
        Task::create(['project_id'=>$p3->id,'channel_id'=>$facts->id,'type'=>'facebook','status'=>'pending','assigned_to'=>$fb->id,'assigned_by'=>$sw->id,'deadline'=>today()->endOfDay(),'video_drive_url'=>'https://drive.google.com/file/d/abc123/view']);
        Task::create(['project_id'=>$p3->id,'channel_id'=>$facts->id,'type'=>'instagram','status'=>'pending','assigned_to'=>$ig->id,'assigned_by'=>$sw->id,'deadline'=>today()->endOfDay(),'video_drive_url'=>'https://drive.google.com/file/d/abc123/view']);
        Task::create(['project_id'=>$p3->id,'channel_id'=>$facts->id,'type'=>'tiktok','status'=>'pending','assigned_to'=>$tt->id,'assigned_by'=>$sw->id,'deadline'=>today()->endOfDay(),'video_drive_url'=>'https://drive.google.com/file/d/abc123/view']);

        // Overdue task
        $p4 = VideoProject::create(['title'=>'Electric vs Gas Truth','slug'=>'ev-vs-gas','channel_id'=>$car->id,'status'=>'idea']);
        Task::create(['project_id'=>$p4->id,'channel_id'=>$car->id,'type'=>'script','status'=>'pending','assigned_to'=>$sw->id,'assigned_by'=>$manager->id,'deadline'=>now()->subDay()]);

        $this->call(PolicySeeder::class);
    }
}