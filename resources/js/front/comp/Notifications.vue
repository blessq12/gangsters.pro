<template>
  <div class="notification-wrapper">
    <button class="btn rounded btn-outline-dark" @click="toggleNotifications">
      <i ref="bellButton" class="fa fa-bell"></i>
      <span v-if="unreadCount" class="badge">{{ unreadCount }}</span>
    </button>
    <transition 
        enter-active-class="animate__animated animate__fadeIn"
        leave-active-class="animate__animated animate__fadeOut"
    >
      <ul v-if="showNotifications" class="notification-list list-unstyled py-2">
        <li v-if="notifications.length === 0" class="p-1">
          <div class="notification-item rounded">
            <span class="notification-message">Новых уведомлений нет</span>
          </div>
        </li>
        <li v-for="(notification, index) in notifications" :key="notification.id" class="p-1">
          <div :class="['notification-item rounded', notification.type]">
            <i :class="notificationIcon(notification.type)"></i>
            <div class="notification-content">
              <span class="notification-message">{{ notification.message }}</span>
              <span class="notification-timestamp">{{ formatTimestamp(notification.created_at) }}</span>
            </div>
            <button @click="removeNotification(index)" class="close-btn">×</button>
          </div>
        </li>
      </ul>
    </transition>
  </div>
</template>

<script>
import axios from 'axios';
import { gsap } from 'gsap';

export default {
  data() {
    return {
      notifications: [],
      showNotifications: false,
      session_id: null
    };
  },
  computed: {
    unreadCount() {
      return this.notifications.length;
    },
  },
  watch: {
    unreadCount(newCount, oldCount) {
      if (newCount > oldCount) {
        this.animateBell();
      }
    }
  },
  created() {
    this.startPolling();
    this.session_id = localStorage.getItem('session_id');
  },
  methods: {
    startPolling() {
      this.pollingInterval = setInterval(this.fetchNotifications, 5000);
    },
    async fetchNotifications() {
      try {
        const response = await axios.get('/api/notifications', {
          params: { session_id: this.session_id }
        });
        // Ensure notifications is always an array
        this.notifications = Array.isArray(response.data.notifications) ? response.data.notifications : [];
      } catch (error) {
        console.error('Error fetching notifications:', error);
        this.notifications = []; // Reset to empty array on error
      }
    },
    toggleNotifications() {
      this.showNotifications = !this.showNotifications;
    },
    removeNotification(index) {
      this.notifications.splice(index, 1);
    },
    animateBell() {
      if (this.$refs.bellButton) {
        gsap.fromTo(this.$refs.bellButton, 
          { rotation: -10 }, 
          { rotation: 10, duration: 0.1, yoyo: true, repeat: -1, ease: "power1.inOut" }
        );
      }
    },
    notificationIcon(type) {
      switch(type) {
        case 'info': return 'fa fa-info-circle';
        case 'success': return 'fa fa-check-circle';
        case 'error': return 'fa fa-exclamation-circle';
        default: return 'fa fa-bell';
      }
    },
    formatTimestamp(timestamp) {
      const date = new Date(timestamp);
      return date.toLocaleString();
    }
  },
  beforeDestroy() {
    clearInterval(this.pollingInterval);
  },
};
</script>

<style lang="sass" scoped>
.notification-wrapper
  position: relative

.notification-list
  position: absolute
  top: 100%
  right: 0
  background-color: white
  border: 1px solid #ccc
  border-radius: 4px
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1)
  width: 300px
  max-height: 400px
  overflow-y: auto
  z-index: 1000

.notification-item
  display: flex
  align-items: center
  padding: 10px
  border-bottom: 1px solid #eee
  position: relative

.notification-item:last-child
  border-bottom: none

.notification-content
  flex-grow: 1
  margin-left: 10px

.notification-message
  display: block

.notification-timestamp
  font-size: 0.8em
  color: #888

.close-btn
  background: none
  border: none
  font-size: 1.2em
  cursor: pointer
  position: absolute
  top: 5px
  right: 5px

.notification-item.info
  background-color: #d9edf7
  color: #31708f

.notification-item.success
  background-color: #dff0d8
  color: #3c763d

.notification-item.error
  background-color: #f2dede
  color: #a94442

.badge
  background-color: red
  color: white
  border-radius: 50%
  padding: 2px 6px
  font-size: 12px
  position: absolute
  top: 0
  right: 0
</style>