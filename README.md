# Real-Time Chat Application (Laravel Reverb)

A **real-time chat platform** built with **Laravel**, **Laravel Reverb**, and **JavaScript (with Echo)**.  
It supports **1-on-1 private messaging**, **group chats**, **typing indicators**, **message read receipts**, and **real-time message broadcasting** — all powered by **Laravel Reverb**, Laravel’s new WebSocket-based broadcasting system.

---

##  Features

###  1-on-1 Private Chat
- Create private conversations between two users.
- Real-time message updates using Laravel Reverb.
- Read receipts when the recipient views the message.
- Typing indicator shows when the other user is typing.

###  Group Chat
- Create and manage group chats.
- Add or remove members (admin-only).
- Real-time group messaging.
- Typing indicators for multiple users.
- Group admins can update group info.

###  Real-Time Events
Powered by **Laravel Reverb**, all events are broadcast instantly:
- `message.sent` – when a message is sent.
- `message.read` – when a message is read.
- `user.typing` / `group.user.typing` – typing indicators.
- `group.message.sent` – when a group message is sent.

###  Other Features
- Secure authentication (only chat members can access messages).
- Paginated message loading.
- Responsive UI (works well on both desktop and mobile).
- Laravel Blade templates for the frontend.

---

##  Tech Stack

| Layer | Technology |
|-------|-------------|
| **Backend** | Laravel 11 |
| **Realtime Engine** | Laravel Reverb |
| **Frontend** | Blade + Vanilla JS |
| **Database** | MySQL / MariaDB |
| **Broadcast Driver** | Reverb (WebSocket-based) |
| **Authentication** | Laravel Breeze |
| **Storage** | Eloquent ORM |
| **Environment** | PHP 8.3+|

---

##  Installation Guide

### 1 Clone the Repository
    git clone https://github.com/your-username/laravel-reverb-chat.git
    cd laravel-reverb-chat

### 2 Install Dependencies
    composer install
    npm install

### 3 Set Up Environment
    cp .env.example .env
Generate the application key: php artisan key:generate

### 4 Configure Reverb
    In your .env file, set the broadcasting driver to reverb:
    BROADCAST_DRIVER=reverb
    REVERB_APP_ID=your-app-id
    REVERB_APP_KEY=your-app-key
    REVERB_APP_SECRET=your-app-secret
    REVERB_HOST=127.0.0.1
    REVERB_PORT=8080

### 5 Run Migrations and Seed Data
    php artisan migrate --seed
    
### 6 Start Reverb Server
    php artisan reverb:start

### 7 Start Laravel Development Server
    php artisan serve

### 8 Compile Frontend Assets
    npm run dev

Key Components
Models
User – Authenticated users of the system.
Chat – Represents both 1-on-1 and group conversations.
Message – Stores chat messages with sender info.

Pivot Tables:
chat_user – User membership in chats.
chat_user_last_seen – Tracks when each user last viewed a chat.
message_user_read – Tracks which messages have been read.

Controllers

ChatController
Handles private (1-on-1) chats.
Broadcasts MessageSent, MessageRead, and UserTyping events.

GroupChatController
Handles group creation, messaging, and member management.

Broadcasts GroupMessageSent and GroupUserTyping events.

Events
MessageSent, MessageRead, UserTyping
GroupMessageSent, GroupUserTyping

Security & Access Control

Only chat participants can:
View chat messages.
Send or read messages.
Receive broadcast updates.
Adding/removing members.

License

This project is open-source and available under the MIT License.

Thabo Makwela

Full-Stack Developer

platformdeveloping@gmail.com


## Preview

https://github.com/user-attachments/assets/f64723da-b5c3-47b9-acc8-e8258276a586

<img width="1361" height="584" alt="Dashboard" src="https://github.com/user-attachments/assets/f8ce0f43-93dc-4b98-80d0-fa2933438c94" />
<img width="1346" height="614" alt="Create_Group" src="https://github.com/user-attachments/assets/307f359c-1eb3-4942-b23a-7250c0446334" />
<img width="1346" height="610" alt="Chats" src="https://github.com/user-attachments/assets/d3716e5c-c5ce-48be-9eea-2f34a3a6b21f" />
<img width="1349" height="613" alt="Messages" src="https://github.com/user-attachments/assets/520bd42d-feb3-461e-9868-5047b4e5813e" />
<img width="1352" height="613" alt="Users" src="https://github.com/user-attachments/assets/0dde39ba-3036-4239-b043-89d1a8ad9016" />
