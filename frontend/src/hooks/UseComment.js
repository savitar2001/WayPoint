import { useState } from 'react';
import { getPostComment, commentOnPost } from '../services/PostService';

const useComment = (userId) => {
  const [comments, setComments] = useState([]);
  const [error, setError] = useState(null);

  const fetchComments = async (postId) => {
    try {
      const response = await getPostComment(postId);
      const commentsData = response.data.data;
      setComments(commentsData);
    } catch (err) {
      setError(`Failed to fetch comments for post with ID ${postId}`);
      console.error(err);
    }
  };

  const submitComment = async (postId,newComment) => {
    try {
      await commentOnPost(userId, postId, newComment);
      setComments((prevComments) => [...prevComments, newComment]);
    } catch (err) {
      setError('Failed to submit comment');
      console.error(err);
    }
  };

  return {
    comments,
    error,
    fetchComments,
    submitComment,
  };
};

export default useComment;