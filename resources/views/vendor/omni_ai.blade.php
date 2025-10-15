@extends('layouts.app')

@section('content')
<style>
/* Simple Professional Chat Interface */
.chat-container {
    display: flex;
    height: calc(100vh - 200px);
    min-height: 500px;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.chat-sidebar {
    width: 250px;
    background: #f8f9fa;
    border-right: 1px solid #ddd;
    padding: 15px;
    overflow-y: auto;
}

.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #fff;
}

.chat-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.chat-status {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #28a745;
}

.status-dot {
    width: 6px;
    height: 6px;
    background: #28a745;
    border-radius: 50%;
}

#chatWindow {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f8f9fa;
    scroll-behavior: smooth;
}

.message {
    margin-bottom: 15px;
}

.message.user {
    text-align: right;
}

.message.assistant {
    text-align: left;
}

.message-content {
    display: inline-block;
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 12px;
    word-wrap: break-word;
    font-size: 14px;
    line-height: 1.4;
}

.message.user .message-content {
    background: #4a90e2;
    color: white;
    box-shadow: 0 2px 4px rgba(74, 144, 226, 0.2);
}

.message.assistant .message-content {
    background: white;
    color: #333;
    border: 1px solid #e0e0e0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Agent-specific styling */
.message.sales-agent {
    border-left: 4px solid #28a745;
}

.message.inventory-agent {
    border-left: 4px solid #ffc107;
}

.message.report-agent {
    border-left: 4px solid #17a2b8;
}

.message.chat-agent {
    border-left: 4px solid #6f42c1;
}

.message-header strong {
    color: #333;
    font-size: 13px;
}

.message.sales-agent .message-header strong {
    color: #28a745;
}

.message.inventory-agent .message-header strong {
    color: #ffc107;
}

.message.report-agent .message-header strong {
    color: #17a2b8;
}

.message.chat-agent .message-header strong {
    color: #6f42c1;
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
    font-size: 11px;
    color: #666;
}

.message-time {
    font-size: 10px;
}

.message-text {
    margin-top: 5px;
    white-space: pre-wrap;
    word-wrap: break-word;
    line-height: 1.5;
}

/* Markdown styling */
.message-text strong {
    font-weight: bold;
    color: #2c3e50;
}

.message-text em {
    font-style: italic;
    color: #7f8c8d;
}

.message-text code {
    background: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    color: #e74c3c;
}

.message-text pre {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    overflow-x: auto;
    margin: 5px 0;
}

.message-text ul, .message-text ol {
    margin: 5px 0;
    padding-left: 20px;
}

.message-text li {
    margin: 3px 0;
}

/* Typing indicator */
.typing {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 12px;
}

.typing-indicator {
    display: flex;
    gap: 3px;
}

.typing-indicator span {
    width: 6px;
    height: 6px;
    background: #007bff;
    border-radius: 50%;
    animation: typing 1.4s infinite ease-in-out;
}

.typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
.typing-indicator span:nth-child(2) { animation-delay: -0.16s; }

@keyframes typing {
    0%, 80%, 100% { opacity: 0.3; }
    40% { opacity: 1; }
}

.typing-text {
    color: #666;
    font-style: italic;
    font-size: 12px;
}

/* Chat input area */
.chat-input-area {
    padding: 15px;
    background: white;
    border-top: 1px solid #ddd;
}

.input-container {
    display: flex;
    gap: 10px;
    align-items: center;
}

#chatInput {
    flex: 1;
    border-radius: 20px;
    padding: 10px 15px;
    border: 1px solid #ddd;
    font-size: 14px;
}

#chatInput:focus {
    border-color: #4a90e2;
    outline: none;
    box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
}

#chatInput:disabled {
    background: #f5f5f5;
    cursor: not-allowed;
}

#sendBtn {
    border-radius: 20px;
    padding: 10px 20px;
    background: #4a90e2;
    border: none;
    color: white;
    font-weight: 500;
    transition: all 0.2s ease;
}

#sendBtn:hover:not(:disabled) {
    background: #357abd;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(74, 144, 226, 0.3);
}

#sendBtn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Suggestions */
.suggestions {
    margin-top: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.suggestion-item {
    padding: 6px 12px;
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 15px;
    cursor: pointer;
    font-size: 12px;
    color: #666;
}

.suggestion-item:hover {
    background: #007bff;
    color: white;
}

/* Action buttons */
.action-buttons {
    margin-top: 10px;
    display: flex;
    gap: 8px;
}

.btn-action {
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 11px;
    border: 1px solid;
    cursor: pointer;
}

.btn-test {
    background: #e3f2fd;
    color: #1976d2;
    border-color: #bbdefb;
}

.btn-clear {
    background: #fff3e0;
    color: #f57c00;
    border-color: #ffcc02;
}

.btn-history {
    background: #f3e5f5;
    color: #7b1fa2;
    border-color: #ce93d8;
}

/* Sidebar styles */
.chat-sidebar {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.sidebar-section {
    margin-bottom: 20px;
}

.sidebar-section:last-child {
    margin-top: auto;
    margin-bottom: 0;
}

.sidebar-section:first-child {
    flex: 1;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

/* Agent Selector Styles */
.agent-selector {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.agent-buttons {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 10px;
}

.agent-btn {
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    border: 1px solid #ddd;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.agent-btn:hover {
    background: #f8f9fa;
    border-color: #007bff;
}

.agent-btn.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.agent-icon {
    font-size: 14px;
}

/* Capabilities Panel */
.capabilities-panel {
    margin-bottom: 20px;
}

.capability-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-top: 10px;
}

.capability-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 8px;
    background: white;
    border-radius: 4px;
    border: 1px solid #e0e0e0;
    font-size: 11px;
    color: #666;
}

.capability-icon {
    font-size: 12px;
}

#chatHistory {
    flex: 1;
    overflow-y: auto;
    max-height: calc(100vh - 400px);
    min-height: 200px;
    padding-right: 5px;
}

/* Custom scrollbar for chat history */
#chatHistory::-webkit-scrollbar {
    width: 6px;
}

#chatHistory::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

#chatHistory::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
    transition: background 0.2s;
}

#chatHistory::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Firefox scrollbar */
#chatHistory {
    scrollbar-width: thin;
    scrollbar-color: #c1c1c1 #f1f1f1;
}

.sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.sidebar-title {
    font-size: 12px;
    font-weight: 600;
    color: #333;
    text-transform: uppercase;
}

.sidebar-controls {
    display: flex;
    gap: 5px;
}

.btn-new-chat {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.btn-new-chat:hover {
    background: #0056b3;
}

.management-buttons {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.btn-clear-current,
.btn-clear-all {
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 11px;
    border: 1px solid;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-clear-current {
    background: #fff3e0;
    color: #f57c00;
    border-color: #ffcc02;
}

.btn-clear-current:hover {
    background: #f57c00;
    color: white;
}

.btn-clear-all {
    background: #ffebee;
    color: #d32f2f;
    border-color: #ffcdd2;
}

.btn-clear-all:hover {
    background: #d32f2f;
    color: white;
}

.history-item {
    padding: 8px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 6px;
    margin-bottom: 5px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.2s;
    position: relative;
}

.history-item:hover {
    background: #f8f9fa;
    border-color: #007bff;
}

.history-item.active {
    background: #e3f2fd;
    border-color: #007bff;
    border-width: 2px;
}

.history-item.active::before {
    content: '';
    position: absolute;
    left: -2px;
    top: 0;
    bottom: 0;
    width: 3px;
    background: #007bff;
    border-radius: 0 2px 2px 0;
}

.history-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.history-item-title {
    font-weight: 600;
    color: #333;
}

.history-item-actions {
    display: flex;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.2s;
}

.history-item:hover .history-item-actions {
    opacity: 1;
}

.history-item-delete {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #ffebee;
    color: #d32f2f;
    border: none;
    cursor: pointer;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.history-item-delete:hover {
    background: #d32f2f;
    color: white;
}

.history-preview {
    font-size: 11px;
    color: #666;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.history-time {
    font-size: 10px;
    color: #999;
    margin-top: 2px;
}

/* Empty state for chat history */
.history-empty {
    text-align: center;
    padding: 20px 10px;
    color: #666;
}

.history-empty-icon {
    font-size: 24px;
    margin-bottom: 8px;
}

.history-empty-text {
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 4px;
}

.history-empty-hint {
    font-size: 10px;
    color: #999;
}

/* Scroll indicator */
.scroll-indicator {
    text-align: center;
    padding: 8px;
    font-size: 10px;
    color: #666;
    background: #f8f9fa;
    border-radius: 4px;
    margin-bottom: 8px;
    border: 1px solid #e0e0e0;
}

/* Agent icon in history */
.history-agent-icon {
    font-size: 12px;
    margin-right: 6px;
}

/* Scrollbar */
#chatWindow::-webkit-scrollbar,
.chat-sidebar::-webkit-scrollbar {
    width: 4px;
}

#chatWindow::-webkit-scrollbar-thumb,
.chat-sidebar::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 2px;
}

/* Agent-specific message styles */
.message.sales-agent .message-content {
    border-left: 4px solid #007bff;
}

.message.inventory-agent .message-content {
    border-left: 4px solid #28a745;
}

.message.report-agent .message-content {
    border-left: 4px solid #ffc107;
}

.message.chat-agent .message-content {
    border-left: 4px solid #6f42c1;
}

/* Proposal messages */
.message.proposal {
    background: #fff3cd;
    border: 2px solid #ffeaa7;
}

.message.proposal .proposal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.message.proposal .proposal-actions {
    display: flex;
    gap: 8px;
    margin-top: 10px;
}

.proposal-btn {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    border: 1px solid;
    cursor: pointer;
}

.btn-approve {
    background: #d4edda;
    color: #155724;
    border-color: #c3e6cb;
}

.btn-reject {
    background: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
}

.btn-modify {
    background: #fff3cd;
    color: #856404;
    border-color: #ffeaa7;
}

/* Search Results Display */
.search-results {
    margin: 10px 0;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
}

.search-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
    font-size: 12px;
    font-weight: 600;
    color: #333;
}

.search-icon {
    font-size: 14px;
}

.search-items {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.search-item {
    padding: 6px 8px;
    background: white;
    border-radius: 4px;
    border: 1px solid #e0e0e0;
    font-size: 11px;
}

.item-name {
    font-weight: 600;
    color: #333;
}

.item-similarity {
    color: #666;
    font-size: 10px;
}

/* Human Approval Modal */
.approval-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.approval-modal.show {
    display: flex;
}

.approval-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.approval-content h3 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 16px;
}

.proposal-details {
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
}

.proposal-type {
    font-weight: 600;
    color: #007bff;
    margin-bottom: 8px;
    font-size: 13px;
}

.proposal-text {
    color: #666;
    font-size: 14px;
    line-height: 1.4;
}

.approval-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.approval-actions button {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

/* Responsive */
@media (max-width: 768px) {
    .chat-container {
        flex-direction: column;
        height: auto;
    }
    
    .chat-sidebar {
        width: 100%;
        height: 150px;
    }
    
    .message-content {
        max-width: 85%;
    }
    
    .agent-buttons {
        flex-direction: row;
        flex-wrap: wrap;
    }
    
    .agent-btn {
        flex: 1;
        min-width: 80px;
    }
    
    .approval-content {
        width: 95%;
        padding: 15px;
    }
    
    .approval-actions {
        flex-direction: column;
    }
}
</style>
<h1 class="page-title">Chat OmniAI </h1>

<div class="chat-container">
    <!-- Sidebar với AI Agents và controls -->
    <div class="chat-sidebar">
        


        

        <!-- Chat History -->
        <div class="sidebar-section">
            <div class="sidebar-header">
                <div class="sidebar-title">📝 Lịch sử trò chuyện</div>
                <div class="sidebar-controls">
                    <button id="newChatBtn" class="btn-new-chat" title="Cuộc trò chuyện mới">
                        <span>+</span>
                    </button>
                </div>
            </div>
            <div id="chatHistory">
                <!-- Lịch sử sẽ được load động -->
            </div>
        </div>
        
        <!-- Management -->
        <div class="sidebar-section">
            <div class="sidebar-title">⚙️ Quản lý</div>
            <div class="management-buttons">
                <button id="clearAllBtn" class="btn-clear-all">Xóa tất cả lịch sử</button>
            </div>
        </div>
    </div>
    
    <!-- Main chat area -->
    <div class="chat-main">
        <div class="chat-header">
            <h3 class="chat-title">Trợ lý dữ liệu nội bộ</h3>
            <div class="chat-status">
                <div class="status-dot"></div>
                <span>Đang hoạt động</span>
            </div>
        </div>
        
        <div id="chatWindow"></div>
        
        <div class="chat-input-area">
            <div class="input-container">
                <input id="chatInput" type="text" placeholder="Tôi có thể giúp gì cho bạn..." />
                <button id="sendBtn">Gửi</button>
            </div>
            
            
            
            
        </div>
    </div>
</div>

<!-- Human Approval Modal -->
<div class="approval-modal" id="approvalModal">
    <div class="approval-content">
        <h3>🔍 Đề xuất cần phê duyệt</h3>
        <div class="proposal-details">
            <div class="proposal-type" id="proposalType">Sales Agent đề xuất:</div>
            <div class="proposal-text" id="proposalText">Đang tải đề xuất...</div>
        </div>
        <div class="approval-actions">
            <button class="btn-approve" onclick="handleApproval('approve')">✅ Phê duyệt</button>
            <button class="btn-reject" onclick="handleApproval('reject')">❌ Từ chối</button>
            <button class="btn-modify" onclick="handleApproval('modify')">✏️ Chỉnh sửa</button>
        </div>
    </div>
</div>

<script>
const chatWindow = document.getElementById('chatWindow');
const chatInput = document.getElementById('chatInput');
const sendBtn = document.getElementById('sendBtn');

// Chat memory để lưu lịch sử cuộc trò chuyện
let conversationHistory = [];
let currentChatId = null;
let savedConversations = [];

// AI Agents state
let currentAgent = 'omni';
let agentConfigs = {
    omni: {
        name: 'OmniAI',
        capabilities: ['Tra cứu', 'Phân tích', 'Gợi ý'],
        context: ['Database', 'LLM Service'],
        icon: '🧠'
    },
    sales: {
        name: 'Sales Agent',
        capabilities: ['Đơn hàng', 'Khách hàng', 'Bán hàng'],
        context: ['Database', 'Vector Store', 'LLM Service'],
        icon: '🛒'
    },
    inventory: {
        name: 'Inventory Agent',
        capabilities: ['Tồn kho', 'Sản phẩm', 'Nhập xuất'],
        context: ['Database', 'Vector Store'],
        icon: '📦'
    },
    report: {
        name: 'Report Agent',
        capabilities: ['Báo cáo', 'KPI', 'Phân tích'],
        context: ['Database', 'LLM Service'],
        icon: '📊'
    }
};


// Display message without adding to conversation history (for loading saved conversations)
function displayMessage(role, text) {
    console.log('displayMessage called:', role, text);
    console.log('Chat window element:', chatWindow);
    console.log('Chat window innerHTML before:', chatWindow.innerHTML.length, 'characters');
    
    const el = document.createElement('div');
    el.className = `message ${role}`;
    
    el.innerHTML = `
        <div class="message-content">
            <div class="message-header">
                <strong>${role === 'user' ? 'Bạn' : 'OmniAI'}</strong>
                <span class="message-time">${new Date().toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'})}</span>
            </div>
            <div class="message-text">${text}</div>
        </div>
    `;
    
    chatWindow.appendChild(el);
    chatWindow.scrollTop = chatWindow.scrollHeight;
    
    console.log('Message displayed in chat window');
    console.log('Chat window innerHTML after:', chatWindow.innerHTML.length, 'characters');
    console.log('Chat window children count:', chatWindow.children.length);
}

async function sendMessage() {
    const msg = chatInput.value.trim();
    if (!msg || sendBtn.disabled) return;
    
    // Disable input và button khi đang gửi
    sendBtn.disabled = true;
    chatInput.disabled = true;
    sendBtn.innerHTML = '⏳ Đang gửi...';
    
    console.log('Sending message:', msg);
    appendMsg('user', msg);
    chatInput.value = '';
    
    // Hiển thị typing indicator
    appendMsg('assistant', '', true);

    let data;
    try {
        console.log('Making API request with history:', conversationHistory);
        const resp = await fetch('/api/ai/simple-llm', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                message: msg,
                agent: currentAgent,
                context: {
                    conversation_history: conversationHistory.slice(0, -1) // Bỏ message vừa gửi
                }
            })
        });
        
        console.log('Response status:', resp.status);
        
        if (!resp.ok) {
            throw new Error(`HTTP ${resp.status}: ${resp.statusText}`);
        }
        
        data = await resp.json();
        
        // Debug: log response để kiểm tra
        console.log('API Response:', data);

        if (data.success !== true && data.success !== 'true') {
            // Xóa typing indicator và hiển thị lỗi
            const typingMsg = chatWindow.querySelector('.message.assistant:last-child');
            if (typingMsg && typingMsg.querySelector('.typing')) {
                typingMsg.remove();
            }
            appendMsg('assistant', `❌ Lỗi: ${data.error || 'Đã có lỗi xảy ra'}`);
            return;
        }
        
        // Xóa typing indicator
        const typingMsg = chatWindow.querySelector('.message.assistant:last-child');
        if (typingMsg && typingMsg.querySelector('.typing')) {
            typingMsg.remove();
        }
        
        console.log('Processing response type:', data.type || 'undefined');
        
        // Lấy Agent name từ response hoặc fallback
        let agentName = data.agent_name || 'OmniAI';
        const responseType = data.type || 'unknown';
        
        // Xử lý response ngay tại đây
        switch (data.type) {
            case 'daily_orders':
                appendMsg('assistant', data.reply || 'Thống kê đơn hàng không khả dụng.', false, agentName);
                break;
            case 'order_lookup':
                const orderFound = data.found === true;
                if (!orderFound) appendMsg('assistant', data.reply || 'Không tìm thấy đơn.', false, agentName);
                else {
                    // Ensure order exists and has required properties
                    const orderData = data.order || {};
                    const orderNumber = orderData.order_number || 'N/A';
                    const customerName = orderData.customer_name || 'N/A';
                    const finalAmount = orderData.final_amount || 'N/A';
                    appendMsg('assistant', data.reply || `Đơn ${orderNumber} - KH: ${customerName} - Tổng: ${finalAmount}`, false, agentName);
                }
                break;
            case 'customer_lookup':
                const customerFound = data.found === true;
                if (!customerFound) appendMsg('assistant', data.reply || 'Không tìm thấy khách hàng.', false, agentName);
                else {
                    // Ensure customer exists and has required properties
                    const customerData = data.customer || {};
                    const customerName = customerData.name || 'N/A';
                    const customerPhone = customerData.phone || 'N/A';
                    const customerEmail = customerData.email || 'N/A';
                    appendMsg('assistant', data.reply || `KH: ${customerName} - SĐT: ${customerPhone} - Email: ${customerEmail}`, false, agentName);
                }
                break;
            case 'sales_analysis':
                appendMsg('assistant', data.reply || 'Phân tích bán hàng không khả dụng.', false, agentName);
                break;
            case 'inventory_check':
                // Ensure products exists and is an array
                const lowStockProducts = Array.isArray(data.products) ? data.products : [];
                const threshold = data.threshold || 5;
                if (!lowStockProducts.length) appendMsg('assistant', `Không có sản phẩm nào có tồn ≤ ${threshold}.`);
                else appendMsg('assistant', `Sản phẩm tồn thấp (≤ ${threshold}): ` + lowStockProducts.map(p => `${p.name}(${p.total_stock})`).join(', '));
                break;
            case 'product_search':
            case 'semantic_search':
            case 'product_recommendation':
                // Ensure products exists and is an array
                const searchProducts = Array.isArray(data.products) ? data.products : [];
                if (!searchProducts.length) appendMsg('assistant', data.reply || 'Không tìm thấy sản phẩm phù hợp.');
                else appendMsg('assistant', data.reply || `Tìm thấy ${searchProducts.length} sản phẩm phù hợp.`);
                break;
            case 'promotions_active':
                // Ensure promotions exists and is an array
                const activePromotions = Array.isArray(data.promotions) ? data.promotions : [];
                if (!activePromotions.length) appendMsg('assistant', 'Hiện không có CTKM đang chạy.');
                else appendMsg('assistant', 'CTKM đang chạy: ' + activePromotions.map(p => `${p.code || p.name}(${p.type})`).join(', '));
                break;
            case 'promotion_simulation':
                // Ensure result exists and has required properties
                const simulationResult = data.result || {};
                const discountTotal = simulationResult.discount_total || 0;
                const shippingDiscount = simulationResult.shipping_discount || 0;
                const appliedPromotions = Array.isArray(simulationResult.applied_promotions) ? simulationResult.applied_promotions : [];
                appendMsg('assistant', `KQ mô phỏng: giảm ${discountTotal} đ; free ship: ${shippingDiscount}; CTKM áp dụng: ${appliedPromotions.length}`);
                break;
            case 'llm':
            case 'general':
            default:
                console.log('LLM response:', data.reply);
                console.log('LLM response type:', typeof data.reply);
                console.log('LLM response length:', data.reply ? data.reply.length : 0);
                appendMsg('assistant', data.reply || 'Đây là câu trả lời từ AI.', false, agentName);
                
                // Check for human approval in any response
                if (data.needs_approval === true && data.proposal) {
                    const proposalData = data.proposal || {};
                    const proposalMessage = proposalData.message || 'Cần phê duyệt';
                    const proposalDetails = proposalData.details || 'Chi tiết không khả dụng';
                    showApprovalModal(proposalMessage, proposalDetails);
                }
                break;
        }
    } catch (error) {
        console.error('Chat error:', error);
        
        // Xóa typing indicator và hiển thị lỗi
        const typingMsg = chatWindow.querySelector('.message.assistant:last-child');
        if (typingMsg && typingMsg.querySelector('.typing')) {
            typingMsg.remove();
        }
        appendMsg('assistant', `❌ Lỗi kết nối: ${error.message}`);
        return;
    } finally {
        // Re-enable input và button
        sendBtn.disabled = false;
        chatInput.disabled = false;
        sendBtn.innerHTML = 'Gửi';
        chatInput.focus();
    }
}

sendBtn.addEventListener('click', sendMessage);
chatInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') sendMessage(); });

// Event listeners for new buttons
document.getElementById('newChatBtn').addEventListener('click', createNewChat);
document.getElementById('clearAllBtn').addEventListener('click', clearAllConversations);

// Update capabilities list based on agent
function updateCapabilitiesList(agentType) {
    const capabilityList = document.getElementById('capabilityList');
    const capabilities = {
        omni: [
            { icon: '📦', text: 'Tra cứu đơn hàng' },
            { icon: '👥', text: 'Quản lý khách hàng' },
            { icon: '📊', text: 'Báo cáo doanh thu' },
            { icon: '🔍', text: 'Tìm kiếm thông minh' }
        ],
        sales: [
            { icon: '🛒', text: 'Xử lý đơn hàng' },
            { icon: '👥', text: 'Quản lý khách hàng' },
            { icon: '💰', text: 'Tính toán giá' },
            { icon: '📈', text: 'Phân tích bán hàng' }
        ],
        inventory: [
            { icon: '📦', text: 'Kiểm tra tồn kho' },
            { icon: '📋', text: 'Quản lý sản phẩm' },
            { icon: '📥', text: 'Nhập kho' },
            { icon: '📤', text: 'Xuất kho' }
        ],
        report: [
            { icon: '📊', text: 'Báo cáo doanh thu' },
            { icon: '📈', text: 'Phân tích KPI' },
            { icon: '📋', text: 'Xuất báo cáo' },
            { icon: '🔍', text: 'Phân tích xu hướng' }
        ]
    };
    
    capabilityList.innerHTML = capabilities[agentType].map(cap => 
        `<div class="capability-item">
            <span class="capability-icon">${cap.icon}</span>
            <span class="capability-text">${cap.text}</span>
        </div>`
    ).join('');
}

// Human Approval functions
function showApprovalModal(proposalType, proposalText) {
    document.getElementById('proposalType').textContent = proposalType;
    document.getElementById('proposalText').textContent = proposalText;
    document.getElementById('approvalModal').classList.add('show');
}

function hideApprovalModal() {
    document.getElementById('approvalModal').classList.remove('show');
}

function handleApproval(action) {
    console.log('Approval action:', action);
    
    // Add approval message to chat
    const actionText = {
        'approve': '✅ Đã phê duyệt đề xuất',
        'reject': '❌ Đã từ chối đề xuất',
        'modify': '✏️ Đang chỉnh sửa đề xuất'
    };
    
    appendMsg('assistant', actionText[action]);
    hideApprovalModal();
}

// Simple markdown processor
function processMarkdown(text) {
    return text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/`(.*?)`/g, '<code>$1</code>')
        .replace(/\n/g, '<br>');
}

// Enhanced message display with agent-specific styling
function appendMsg(role, text, isTyping = false, agentName = null) {
    const el = document.createElement('div');
    let className = `message ${role}`;
    
    // Add agent-specific class if specified
    if (agentName && role === 'assistant') {
        className += ` ${agentName.toLowerCase().replace(' ', '-')}-agent`;
    }
    
    el.className = className;
    
    if (isTyping) {
        el.innerHTML = `
            <div class="message-content typing">
                <div class="typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span class="typing-text">${agentName || 'OmniAI'} đang trả lời...</span>
            </div>
        `;
    } else {
        el.innerHTML = `
            <div class="message-content">
                <div class="message-header">
                    <strong>${role === 'user' ? 'Bạn' : (agentName || 'OmniAI')}</strong>
                    <span class="message-time">${new Date().toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'})}</span>
                </div>
                <div class="message-text">${processMarkdown(text)}</div>
            </div>
        `;
    }
    
    chatWindow.appendChild(el);
    chatWindow.scrollTop = chatWindow.scrollHeight;
    
    // Lưu vào conversation history (chỉ khi không phải typing)
    if (!isTyping) {
        conversationHistory.push({
            role: role === 'user' ? 'user' : 'assistant',
            content: text,
            agent: agentName || 'OmniAI'
        });
        
        // Giới hạn history để tránh token quá nhiều (giữ lại 10 messages gần nhất)
        if (conversationHistory.length > 10) {
            conversationHistory = conversationHistory.slice(-10);
        }
        
        // Tự động lưu cuộc hội thoại sau mỗi 3 tin nhắn
        if (conversationHistory.length % 3 === 0 && conversationHistory.length > 0) {
            saveCurrentConversation();
        }
        
        // Luôn lưu conversation hiện tại để restore khi reload
        saveCurrentConversationToStorage();
    }
}

// Event delegation for chat history
document.addEventListener('click', function(e) {
    console.log('Document clicked:', e.target);
    console.log('Target classes:', e.target.className);
    console.log('Target closest history-item:', e.target.closest('.history-item'));
    console.log('Target closest #chatHistory:', e.target.closest('#chatHistory'));
    
    // Handle chat item click
    if (e.target.closest('.history-item') && e.target.closest('#chatHistory')) {
        const historyItem = e.target.closest('.history-item');
        const chatId = historyItem.getAttribute('data-chat-id');
        console.log('Chat item clicked, chatId:', chatId);
        console.log('History item element:', historyItem);
        console.log('Target is delete button:', e.target.classList.contains('history-item-delete'));
        
        if (chatId && !e.target.classList.contains('history-item-delete')) {
            console.log('Loading chat history for:', chatId);
            loadChatHistory(chatId);
        }
    }
    
    // Handle delete button click
    if (e.target.classList.contains('history-item-delete')) {
        e.stopPropagation();
        const chatId = e.target.getAttribute('data-chat-id');
        console.log('Delete button clicked, chatId:', chatId);
        if (chatId) {
            deleteChat(chatId, e);
        }
    }
});


// Generate conversation summary
function generateConversationSummary() {
    if (conversationHistory.length === 0) return 'Cuộc trò chuyện trống';
    
    const userMessages = conversationHistory.filter(msg => msg.role === 'user');
    if (userMessages.length === 0) return 'Chỉ có phản hồi từ AI';
    
    const topics = userMessages.map(msg => msg.content.substring(0, 30)).join(', ');
    return `${topics}... (${conversationHistory.length} tin nhắn)`;
}


// Update chat history sidebar
function updateChatHistorySidebar() {
    const historyContainer = document.getElementById('chatHistory');
    console.log('Updating chat history sidebar, conversations:', savedConversations.length);
    console.log('Current chat ID:', currentChatId);
    
    if (savedConversations.length === 0) {
        historyContainer.innerHTML = `
            <div class="history-empty">
                <div class="history-empty-icon">💬</div>
                <div class="history-empty-text">Chưa có lịch sử trò chuyện</div>
                <div class="history-empty-hint">Bắt đầu cuộc trò chuyện mới!</div>
            </div>
        `;
        return;
    }
    
    // Show scroll indicator if there are many conversations
    const showScrollIndicator = savedConversations.length > 8;
    
    historyContainer.innerHTML = `
        ${showScrollIndicator ? '<div class="scroll-indicator">📜 Cuộn để xem thêm</div>' : ''}
        ${savedConversations.map((conv) => {
            const date = new Date(conv.timestamp);
            const timeStr = date.toLocaleString('vi-VN', { 
                hour: '2-digit', 
                minute: '2-digit',
                day: '2-digit',
                month: '2-digit'
            });
            
            const isActive = currentChatId === conv.id ? 'active' : '';
            const agentIcon = getAgentIcon(conv.agent || 'omni');
            
            console.log(`Rendering conversation ${conv.id}, active: ${isActive}, currentChatId: ${currentChatId}`);
            
            return `
                <div class="history-item ${isActive}" data-chat-id="${conv.id}">
                    <div class="history-item-header">
                        <div class="history-item-title">
                            <span class="history-agent-icon">${agentIcon}</span>
                            ${conv.title || 'Cuộc trò chuyện'}
                        </div>
                        <div class="history-item-actions">
                            <button class="history-item-delete" data-chat-id="${conv.id}" title="Xóa cuộc trò chuyện">×</button>
                        </div>
                    </div>
                    <div class="history-preview">${conv.summary}</div>
                    <div class="history-time">${timeStr}</div>
                </div>
            `;
        }).join('')}
    `;
}

// Get agent icon based on agent type
function getAgentIcon(agentType) {
    const agentIcons = {
        'omni': '🧠',
        'sales': '🛒',
        'inventory': '📦',
        'report': '📊',
        'chat': '💬'
    };
    return agentIcons[agentType] || '🧠';
}

// Test API trực tiếp
window.testAPI = async function() {
    try {
        const resp = await fetch('/api/ai/simple-llm', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ message: 'test' })
        });
        
        const data = await resp.json();
        console.log('Direct API test:', data);
        appendMsg('assistant', `API Test: ${JSON.stringify(data)}`);
    } catch (error) {
        console.error('Direct API test error:', error);
        appendMsg('assistant', `API Test Error: ${error.message}`);
    }
};

// Test function để tạo lịch sử chat
window.testCreateHistory = function() {
    console.log('Creating test conversation...');
    
    // Thêm tin nhắn test
    conversationHistory.push({
        role: 'user',
        content: 'Test message from user'
    });
    
    conversationHistory.push({
        role: 'assistant', 
        content: 'Test response from AI'
    });
    
    // Lưu cuộc trò chuyện
    saveCurrentConversation();
    
    console.log('Test conversation created, total conversations:', savedConversations.length);
};

// Test function để kiểm tra click
window.testClick = function() {
    console.log('Testing click functionality...');
    console.log('Available conversations:', savedConversations);
    console.log('History items in DOM:', document.querySelectorAll('.history-item').length);
    
    // Simulate click on first history item
    const firstItem = document.querySelector('.history-item[data-chat-id]');
    if (firstItem) {
        console.log('First item found:', firstItem);
        console.log('Chat ID:', firstItem.getAttribute('data-chat-id'));
        firstItem.click();
    } else {
        console.log('No history items found');
    }
};

// Debug function để kiểm tra trạng thái
window.debugState = function() {
    console.log('=== DEBUG STATE ===');
    console.log('Current Chat ID:', currentChatId);
    console.log('Conversation History Length:', conversationHistory.length);
    console.log('Saved Conversations Length:', savedConversations.length);
    console.log('Current Messages:', conversationHistory);
    console.log('Saved Conversations:', savedConversations);
    
    const stored = localStorage.getItem('chatHistory');
    console.log('LocalStorage Data:', stored ? JSON.parse(stored) : 'No data');
    
    const historyItems = document.querySelectorAll('.history-item');
    console.log('DOM History Items:', historyItems.length);
    historyItems.forEach((item, index) => {
        console.log(`Item ${index}:`, item.getAttribute('data-chat-id'));
    });
};

// Test function để tạo và load conversation
window.testFullFlow = function() {
    console.log('=== TESTING FULL FLOW ===');
    
    // Step 1: Create conversation
    console.log('Step 1: Creating conversation...');
    conversationHistory = [];
    currentChatId = generateChatId();
    
    conversationHistory.push({
        role: 'user',
        content: 'Test message 1'
    });
    
    conversationHistory.push({
        role: 'assistant',
        content: 'Test response 1'
    });
    
    console.log('Created conversation with', conversationHistory.length, 'messages');
    
    // Step 2: Save conversation
    console.log('Step 2: Saving conversation...');
    saveCurrentConversation();
    console.log('Saved. Total conversations:', savedConversations.length);
    
    // Step 3: Load conversation
    console.log('Step 3: Loading conversation...');
    if (savedConversations.length > 0) {
        const firstConv = savedConversations[0];
        console.log('Loading conversation:', firstConv.id);
        loadChatHistory(firstConv.id);
    }
    
    console.log('=== TEST COMPLETE ===');
};

// Debug function để kiểm tra localStorage
window.debugStorage = function() {
    console.log('=== DEBUG STORAGE ===');
    const chatHistory = localStorage.getItem('chatHistory');
    const currentConversation = localStorage.getItem('currentConversation');
    
    console.log('Chat History:', chatHistory ? JSON.parse(chatHistory) : 'No data');
    console.log('Current Conversation:', currentConversation ? JSON.parse(currentConversation) : 'No data');
    console.log('Current Chat ID:', currentChatId);
    console.log('Conversation History Length:', conversationHistory.length);
    console.log('Saved Conversations Length:', savedConversations.length);
};

// Test function để test restore
window.testRestore = function() {
    console.log('=== TEST RESTORE ===');
    
    // Clear current state
    conversationHistory = [];
    currentChatId = null;
    
    // Try to restore
    const currentConversation = localStorage.getItem('currentConversation');
    if (currentConversation) {
        try {
            const data = JSON.parse(currentConversation);
            conversationHistory = Array.isArray(data.messages) ? data.messages : [];
            currentChatId = data.chatId || generateChatId();
            console.log('Restored conversation:', conversationHistory.length, 'messages');
            
            // Clear chat window
            chatWindow.innerHTML = '';
            
            // Display restored messages
            conversationHistory.forEach(msg => {
                displayMessage(msg.role === 'user' ? 'user' : 'assistant', msg.content);
            });
        } catch (error) {
            console.error('Error restoring conversation:', error);
        }
    } else {
        console.log('No current conversation to restore');
    }
};

// Test function để test click
window.testClickHistory = function() {
    console.log('=== TEST CLICK HISTORY ===');
    console.log('Available conversations:', savedConversations.length);
    console.log('History items in DOM:', document.querySelectorAll('.history-item').length);
    
    const historyItems = document.querySelectorAll('.history-item[data-chat-id]');
    console.log('History items with data-chat-id:', historyItems.length);
    
    historyItems.forEach((item, index) => {
        console.log(`Item ${index}:`, item.getAttribute('data-chat-id'));
    });
    
    if (historyItems.length > 0) {
        const firstItem = historyItems[0];
        const chatId = firstItem.getAttribute('data-chat-id');
        console.log('Clicking first item with chatId:', chatId);
        firstItem.click();
    } else {
        console.log('No history items to click');
    }
};

// Test function để tạo conversation test
window.createTestConversation = function() {
    console.log('=== CREATING TEST CONVERSATION ===');
    
    // Clear current
    chatWindow.innerHTML = '';
    conversationHistory = [];
    currentChatId = generateChatId();
    
    // Add test messages
    conversationHistory.push({
        role: 'user',
        content: 'Xin chào, tôi muốn hỏi về nước hoa'
    });
    
    conversationHistory.push({
        role: 'assistant',
        content: 'Chào bạn! Tôi có thể giúp gì về nước hoa?'
    });
    
    conversationHistory.push({
        role: 'user',
        content: 'Có nước hoa nam nào không?'
    });
    
    conversationHistory.push({
        role: 'assistant',
        content: 'Có nhiều nước hoa nam đẹp lắm! Bạn thích mùi gì?'
    });
    
    // Display messages
    conversationHistory.forEach(msg => {
        displayMessage(msg.role === 'user' ? 'user' : 'assistant', msg.content);
    });
    
    // Save conversation
    saveCurrentConversation();
    saveCurrentConversationToStorage();
    
    console.log('Created test conversation with', conversationHistory.length, 'messages');
    console.log('Chat ID:', currentChatId);
};

// Initialize chat history sidebar on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== INITIALIZING AI AGENTS CHAT ===');
    
    // Initialize AI Agents
    initializeAgents();
    
    // Load saved conversations
    loadSavedConversations();
    updateChatHistorySidebar();
    
    // Initialize current chat ID if not set
    if (!currentChatId) {
        currentChatId = generateChatId();
    }
    
    // Try to restore current conversation from localStorage
    const currentConversation = localStorage.getItem('currentConversation');
    if (currentConversation) {
        try {
            const data = JSON.parse(currentConversation);
            conversationHistory = Array.isArray(data.messages) ? data.messages : [];
            currentChatId = data.chatId || currentChatId;
            console.log('Restored current conversation:', conversationHistory.length, 'messages');
            console.log('Current chat ID:', currentChatId);
            
            // Clear chat window first
            chatWindow.innerHTML = '';
            
            // Display restored messages
            conversationHistory.forEach((msg, index) => {
                console.log(`Restoring message ${index + 1}:`, msg.role, msg.content);
                displayMessage(msg.role === 'user' ? 'user' : 'assistant', msg.content);
            });
            
            console.log('Conversation restored successfully');
            console.log('Chat window children count:', chatWindow.children.length);
        } catch (error) {
            console.error('Error restoring conversation:', error);
            conversationHistory = [];
        }
    }
    
    // Only show welcome message if no conversation history exists
    if (conversationHistory.length === 0) {
        console.log('No conversation to restore, showing welcome message');
        setTimeout(() => {
            const config = agentConfigs[currentAgent];
            appendMsg('assistant', `Xin chào! Tôi là ${config.name} ${config.icon} - trợ lý nội bộ của bạn. Tôi có thể giúp bạn ${config.capabilities.join(', ')}. Hãy thử hỏi tôi bất cứ điều gì!`);
        }, 500);
    } else {
        console.log('Conversation restored, not showing welcome message');
    }
    
    console.log('=== AI AGENTS INITIALIZATION COMPLETE ===');
});

// Initialize AI Agents
function initializeAgents() {
    console.log('Initializing AI Agents...');
    
    // Set initial agent
    currentAgent = 'omni';
    
    // Update capabilities list
    updateCapabilitiesList(currentAgent);
    
    console.log('AI Agents initialized:', agentConfigs[currentAgent].name);
}

// Load saved conversations from localStorage
function loadSavedConversations() {
    const saved = localStorage.getItem('chatHistory');
    savedConversations = saved ? JSON.parse(saved) : [];
}

// Generate unique chat ID
function generateChatId() {
    return 'chat_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
}

// Create new chat
function createNewChat() {
    // Save current conversation if it has messages
    if (conversationHistory.length > 0) {
        saveCurrentConversation();
    }
    
    // Clear current chat
    chatWindow.innerHTML = '';
    conversationHistory = [];
    currentChatId = generateChatId();
    
    // Remove active class from all history items
    document.querySelectorAll('.history-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Save current conversation to storage
    saveCurrentConversationToStorage();
    
    // Welcome message for new chat
    setTimeout(() => {
        appendMsg('assistant', 'Cuộc trò chuyện mới đã được tạo! Tôi sẵn sàng hỗ trợ bạn.');
    }, 300);
}

// Save current conversation
function saveCurrentConversation() {
    if (conversationHistory.length === 0) {
        console.log('No conversation to save, history is empty');
        return;
    }
    
    console.log('Saving current conversation, messages:', conversationHistory.length);
    
    const conversationData = {
        id: currentChatId || generateChatId(),
        timestamp: new Date().toISOString(),
        messages: [...conversationHistory],
        summary: generateConversationSummary(),
        title: generateConversationTitle()
    };
    
    console.log('Conversation data:', conversationData);
    
    // Remove existing conversation with same ID
    savedConversations = savedConversations.filter(conv => conv.id !== conversationData.id);
    
    // Add new conversation to the beginning
    savedConversations.unshift(conversationData);
    
    // Keep only last 50 conversations
    if (savedConversations.length > 50) {
        savedConversations = savedConversations.slice(0, 50);
    }
    
    localStorage.setItem('chatHistory', JSON.stringify(savedConversations));
    
    // Also save current conversation
    saveCurrentConversationToStorage();
    
    updateChatHistorySidebar();
}

// Save current conversation to localStorage for restoration
function saveCurrentConversationToStorage() {
    const currentData = {
        chatId: currentChatId,
        messages: [...conversationHistory],
        timestamp: new Date().toISOString()
    };
    localStorage.setItem('currentConversation', JSON.stringify(currentData));
    console.log('Saved current conversation to storage:', conversationHistory.length, 'messages');
}

// Generate conversation title
function generateConversationTitle() {
    if (conversationHistory.length === 0) return 'Cuộc trò chuyện trống';
    
    const firstUserMessage = conversationHistory.find(msg => msg.role === 'user');
    if (firstUserMessage) {
        const title = firstUserMessage.content.substring(0, 30);
        return title.length < firstUserMessage.content.length ? title + '...' : title;
    }
    
    return 'Cuộc trò chuyện ' + new Date().toLocaleDateString('vi-VN');
}

// Load specific chat history
function loadChatHistory(chatId) {
    console.log('Loading chat history:', chatId);
    console.log('Available conversations:', savedConversations.map(c => c.id));
    
    const conversation = savedConversations.find(conv => conv.id === chatId);
    if (!conversation) {
        console.error('Conversation not found:', chatId);
        return;
    }
    
    // Save current conversation if it has messages
    if (conversationHistory.length > 0) {
        saveCurrentConversation();
    }
    
    // Clear current chat
    chatWindow.innerHTML = '';
    
    // Set conversation data
    conversationHistory = [...conversation.messages];
    currentChatId = conversation.id;
    
    console.log('Set conversationHistory to:', conversationHistory.length, 'messages');
    console.log('Set currentChatId to:', currentChatId);
    
    // Display conversation messages (without adding to conversationHistory)
    conversation.messages.forEach((msg, index) => {
        console.log(`Displaying message ${index + 1}:`, msg.role, msg.content);
        displayMessage(msg.role === 'user' ? 'user' : 'assistant', msg.content);
    });
    
    console.log('Displayed', conversation.messages.length, 'messages from conversation:', conversation.id);
    console.log('Chat window children count:', chatWindow.children.length);
    
    console.log('Loaded conversation. Messages count:', conversationHistory.length);
    console.log('Current chat ID:', currentChatId);
    
    // Save current conversation to storage
    saveCurrentConversationToStorage();
    
    // Update active state
    document.querySelectorAll('.history-item').forEach(item => {
        item.classList.remove('active');
    });
    
    const activeItem = document.querySelector(`[data-chat-id="${chatId}"]`);
    if (activeItem) {
        activeItem.classList.add('active');
    }
    
    // Scroll to bottom
    chatWindow.scrollTop = chatWindow.scrollHeight;
}

// Delete specific chat
function deleteChat(chatId, event = null) {
    if (event) {
        event.stopPropagation(); // Prevent loading the chat
    }
    
    if (confirm('Bạn có chắc muốn xóa cuộc trò chuyện này?')) {
        savedConversations = savedConversations.filter(conv => conv.id !== chatId);
        localStorage.setItem('chatHistory', JSON.stringify(savedConversations));
        updateChatHistorySidebar();
        
        // If deleted chat was current, create new chat
        if (currentChatId === chatId) {
            createNewChat();
        }
    }
}

// Clear current conversation
function clearCurrentConversation() {
    if (conversationHistory.length === 0) {
        alert('Cuộc trò chuyện hiện tại đã trống!');
        return;
    }
    
    if (confirm('Bạn có chắc muốn xóa cuộc trò chuyện hiện tại?')) {
        chatWindow.innerHTML = '';
        conversationHistory = [];
        currentChatId = null;
        
        // Remove active class
        document.querySelectorAll('.history-item').forEach(item => {
            item.classList.remove('active');
        });
        
        appendMsg('assistant', 'Cuộc trò chuyện đã được xóa. Tôi sẵn sàng hỗ trợ bạn!');
        
        // Save current conversation to storage
        saveCurrentConversationToStorage();
    }
}

// Clear all conversations
function clearAllConversations() {
    if (savedConversations.length === 0) {
        alert('Không có lịch sử trò chuyện nào để xóa!');
        return;
    }
    
    if (confirm('Bạn có chắc muốn xóa TẤT CẢ lịch sử trò chuyện? Hành động này không thể hoàn tác!')) {
        savedConversations = [];
    localStorage.removeItem('chatHistory');
    localStorage.removeItem('currentConversation');
    updateChatHistorySidebar();
        createNewChat();
    }
}
</script>
@endsection


