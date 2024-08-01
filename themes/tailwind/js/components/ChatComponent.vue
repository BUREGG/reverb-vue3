<template>
    <div>
      <div class="flex flex-col justify-end h-80">
        <div ref="messagesContainer" class="p-4 overflow-y-auto max-h-fit">
          <div
            v-for="message in messages"
            :key="message.id"
            class="flex items-center mb-2"
          >
            <div
              v-if="message.sender_id === currentUser.id"
              class="p-2 ml-auto text-white bg-blue-500 rounded-lg"
            >
              {{ message.text }}
              <a v-if="message.attachment" :href="`/storage/${message.attachment}`" target="_blank" download>
                {{ message.attachment_name }}
              </a>
            </div>
            <div v-else class="p-2 mr-auto bg-gray-200 rounded-lg">
              {{ message.text }}
              <a v-if="message.attachment" :href="`/storage/${message.attachment}`" target="_blank" download>
                {{ message.attachment_name }}
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="flex items-center">
        <input
          type="text"
          v-model="newMessage"
          @keydown="sendTypingEvent"
          @keyup.enter="sendMessage"
          placeholder="Type a message..."
          class="flex-1 px-2 py-1 border rounded-lg"
        />
        <input
          type="file"
          ref="fileInput"
          @change="handleFileUpload"
          class="ml-2"
        />
        <button
          @click="sendMessage"
          :disabled="!canSendMessage"
          class="px-4 py-1 ml-2 text-white bg-blue-500 rounded-lg"
        >
          Send
        </button>
      </div>
      <small v-if="isFriendTyping" class="text-gray-700">
        {{ friend.name }} is typing...
      </small>
      <div v-if="errorMessage" class="mt-2 text-red-600">
        {{ errorMessage }}
      </div>
    </div>
  </template>
  
  <script setup>
  import axios from "axios";
  import { nextTick, onMounted, ref, watch, computed } from "vue";
  import { useToast } from 'vue-toastification';
  
  const props = defineProps({
    friend: {
      type: Object,
      required: true,
    },
    currentUser: {
      type: Object,
      required: true,
    },
  });
  
  const messages = ref([]);
  const newMessage = ref("");
  const messagesContainer = ref(null);
  const isFriendTyping = ref(false);
  const isFriendTypingTimer = ref(null);
  const fileInput = ref(null);
  const selectedFile = ref(null);
  const errorMessage = ref("");
  const toast = useToast();
  
  const canSendMessage = computed(() => {
    return newMessage.value.trim() !== "" || selectedFile.value;
  });
  
  watch(
    messages,
    () => {
      nextTick(() => {
        messagesContainer.value.scrollTo({
          top: messagesContainer.value.scrollHeight,
          behavior: "smooth",
        });
      });
    },
    { deep: true }
  );
  
  const handleFileUpload = () => {
    const file = fileInput.value.files[0];
    if (file) {
      if (file.size > 2 * 1024 * 1024) { 
        errorMessage.value = "File size exceeds 2 MB.";
        toast.error("File size exceeds 2 MB.");
        selectedFile.value = null;
        fileInput.value.value = "";
      } else if (!['image/jpeg', 'image/png', 'application/pdf'].includes(file.type)) {
        errorMessage.value = "Unsupported file type.";
        toast.error("Unsupported file type.");
        selectedFile.value = null;
        fileInput.value.value = "";
      } else {
        errorMessage.value = "";
        selectedFile.value = file;
      }
    }
  };
  
  const sendMessage = () => {
    if (canSendMessage.value) {
      const formData = new FormData();
      formData.append("message", newMessage.value);
      if (selectedFile.value) {
        formData.append("attachment", selectedFile.value);
      }
  
      axios
        .post(`/messages/${props.friend.id}`, formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
        .then((response) => {
          messages.value.push(response.data);
          newMessage.value = "";
          selectedFile.value = null;
          fileInput.value.value = "";
        })
        .catch((error) => {
          errorMessage.value = "Failed to send message.";
          toast.error("Failed to send message.");
        });
    } else {
      errorMessage.value = "Cannot send an empty message.";
      toast.error("Cannot send an empty message.");
    }
  };
  
  const sendTypingEvent = () => {
    Echo.private(`chat.${props.friend.id}`).whisper("typing", {
      userID: props.currentUser.id,
    });
  };
  
  onMounted(() => {
    axios.get(`/messages/${props.friend.id}`).then((response) => {
      console.log(response.data);
      messages.value = response.data;
    });
  
    Echo.private(`chat.${props.currentUser.id}`)
      .listen("MessageSent", (response) => {
        messages.value.push(response.message);
      })
      .listenForWhisper("typing", (response) => {
        isFriendTyping.value = response.userID === props.friend.id;
  
        if (isFriendTypingTimer.value) {
          clearTimeout(isFriendTypingTimer.value);
        }
  
        isFriendTypingTimer.value = setTimeout(() => {
          isFriendTyping.value = false;
        }, 1000);
      });
  });
  </script>  