<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Messages</h2>
            <p class="text-sm text-gray-500 mt-1">Chat between doctors and MRI technicians.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" style="height: 75vh;">
        <div class="grid grid-cols-12 h-full">
            {{-- Contacts sidebar --}}
            <div class="col-span-12 md:col-span-4 lg:col-span-3 border-r border-gray-200 flex flex-col">
                <div class="p-4 border-b border-gray-200">
                    <div class="relative">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search contacts..."
                            class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto">
                    @forelse($contacts as $contact)
                        <button wire:click="selectUser({{ $contact->id }})"
                            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition border-b border-gray-100 text-left
                                {{ $selectedUserId == $contact->id ? 'bg-blue-50' : '' }}">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold flex-shrink-0">
                                {{ strtoupper(substr($contact->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $contact->name }}</p>
                                    @if($contact->unread_count > 0)
                                        <span class="ml-2 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-semibold text-white bg-blue-600 rounded-full">
                                            {{ $contact->unread_count }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 truncate">
                                    {{ ucfirst(str_replace('_', ' ', $contact->role)) }}
                                    @if($contact->last_message)
                                        — {{ \Illuminate\Support\Str::limit($contact->last_message->body, 30) }}
                                    @endif
                                </p>
                            </div>
                        </button>
                    @empty
                        <div class="p-6 text-center text-sm text-gray-500">No contacts available</div>
                    @endforelse
                </div>
            </div>

            {{-- Conversation pane --}}
            <div class="col-span-12 md:col-span-8 lg:col-span-9 flex flex-col">
                @if($selectedUser)
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold">
                            {{ strtoupper(substr($selectedUser->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $selectedUser->name }}</p>
                            <p class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $selectedUser->role)) }}</p>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6 space-y-3 bg-gray-50" id="message-list">
                        @forelse($messages as $message)
                            @php $mine = $message['sender_id'] == Auth::id(); @endphp
                            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-md px-4 py-2.5 rounded-2xl shadow-sm
                                    {{ $mine ? 'bg-blue-600 text-white rounded-br-sm' : 'bg-white text-gray-900 rounded-bl-sm border border-gray-200' }}">
                                    <p class="text-sm whitespace-pre-wrap break-words">{{ $message['body'] }}</p>
                                    <p class="text-[10px] mt-1 {{ $mine ? 'text-blue-100' : 'text-gray-400' }}">
                                        {{ \Carbon\Carbon::parse($message['created_at'])->format('M d, H:i') }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="flex items-center justify-center h-full">
                                <p class="text-sm text-gray-500">No messages yet — say hello.</p>
                            </div>
                        @endforelse
                    </div>

                    <form wire:submit.prevent="sendMessage" class="px-4 py-3 border-t border-gray-200 bg-white flex gap-2">
                        <input wire:model="body" type="text" placeholder="Type a message..."
                            class="flex-1 px-4 py-2.5 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            autocomplete="off">
                        <button type="submit"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-full shadow-sm transition disabled:opacity-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Send
                        </button>
                    </form>
                @else
                    <div class="flex-1 flex items-center justify-center text-center px-6">
                        <div>
                            <div class="w-16 h-16 mx-auto rounded-full bg-blue-50 flex items-center justify-center mb-3">
                                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-700 font-medium">Select a contact to start chatting</p>
                            <p class="text-sm text-gray-500 mt-1">Pick someone from the list on the left.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            const scrollToBottom = () => {
                const list = document.getElementById('message-list');
                if (list) list.scrollTop = list.scrollHeight;
            };
            scrollToBottom();
            Livewire.hook('morph.updated', scrollToBottom);
        });
    </script>
</div>
