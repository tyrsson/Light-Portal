<template>
  <li
    ref="parent"
    :id="`comment${comment.id}`"
    :class="['col-xs-12', 'generic_list_wrapper', 'bg', ['even', 'odd'][index % 2]]"
    :data-id="comment.id"
    :data-author="comment.poster.id"
    itemprop="comment"
    itemscope
    itemtype="https://schema.org/Comment"
  >
    <div class="comment_wrapper" :id="`comment=${comment.id}`">
      <div class="comment_avatar">
        <span v-html="comment.poster.avatar"></span>
        <span v-if="comment.authorial" class="new_posts">{{ $t('author') }}</span>
      </div>
      <div class="comment_entry bg" :class="['odd', 'even'][index % 2]">
        <div class="comment_title">
          <span
            class="bg"
            :class="['even', 'odd'][index % 2]"
            :data-id="comment.id"
            itemprop="creator"
            v-text="comment.poster.name"
          ></span>
          <div class="comment_date bg" :class="['even', 'odd'][index % 2]">
            <span itemprop="datePublished" :content="comment.published_at">
              <span v-html="comment.human_date"></span>
              <a class="bbc_link" :href="`#comment=${comment.id}`" v-text="`#${comment.id}`"></a>
            </span>
          </div>
        </div>

        <template v-if="editMode">
          <EditForm :comment="comment" @submit="update" @cancel="editMode = false"></EditForm>
        </template>
        <template v-else>
          <MarkdownPreview
            :content="comment.message"
            class="comment_content"
            :style="{ border: 'none' }"
            itemprop="text"
          />
          <div v-if="userId" class="comment_buttons">
            <Button v-if="showReplyButton" @click="replyMode = !replyMode" tag="span" icon="reply">
              {{ $t('reply') }}
            </Button>
            <Button v-if="canEdit" @click="editMode = true" tag="span" icon="edit">
              {{ $t('modify') }}
            </Button>
            <template v-for="button in comment.extra_buttons">
              <span v-html="button"></span>
            </template>
            <Button
              v-if="showRemoveButton"
              :class="isHover ? 'error' : undefined"
              @mouseover="isHover = true"
              @mouseleave="isHover = false"
              @click.prevent="remove($el.dataset.id)"
              tag="span"
              icon="remove"
            >
              {{ $t('remove') }}
            </Button>
          </div>
        </template>
      </div>

      <keep-alive>
        <ReplyForm v-if="replyMode" :parent="$el.dataset" @submit="add">
          <Button class="active" @click.self="replyMode = false">{{ $t('modify_cancel') }}</Button>
        </ReplyForm>
      </keep-alive>

      <ListTransition v-if="comment.replies" class="comment_list row">
        <CommentItem
          v-for="reply in comment.replies"
          :key="reply.id"
          :comment="reply"
          :index="index + 1"
          :level="level + 1"
          @add-comment="add"
          @update-comment="update"
          @remove-comment="remove"
        />
      </ListTransition>
    </div>
  </li>
</template>

<script setup>
import { defineOptions, defineEmits, ref, computed } from 'vue';
import { useUserStore } from '@scripts/comment_stores.js';
import ListTransition from './ListTransition.vue';
import EditForm from './EditForm.vue';
import ReplyForm from './ReplyForm.vue';
import MarkdownPreview from './MarkdownPreview.vue';
import Button from './BaseButton.vue';

defineOptions({
  name: 'CommentItem',
});

const { id: userId, is_admin: isAdmin } = useUserStore();

const props = defineProps({
  comment: {
    type: {
      id: Number,
      published_at: Number,
      human_date: String,
      can_edit: Boolean,
      authorial: Boolean,
      poster: {
        id: Number,
        name: String,
        avatar: String,
      },
      extra_buttons: Array,
      replies: Array,
    },
    required: true,
  },
  index: {
    type: Number,
    required: true,
  },
  level: {
    type: Number,
    required: false,
    default: 1,
  },
});

const emit = defineEmits(['add-comment', 'update-comment', 'remove-comment']);

const isHover = ref(false);
const replyMode = ref(false);
const editMode = ref(false);
const parent = ref();

const showReplyButton = computed(() => props.level < 5 && userId !== props.comment.poster.id);

const showRemoveButton = computed(
  () => props.comment.poster.id === userId || isAdmin
);

const canEdit = computed(
  () =>
    props.comment.can_edit &&
    (!props.comment.replies || !props.comment.replies.length) &&
    props.comment.poster.id === userId
);

const add = (comment) => {
  emit('add-comment', comment);
  replyMode.value = false;
};

const update = (comment) => {
  emit('update-comment', comment);
  props.comment.message = comment.content;
  editMode.value = false;
};

const remove = (id) => {
  emit('remove-comment', id);
};
</script>
