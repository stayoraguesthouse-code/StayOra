<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deployment Manager SO| stayora.com.pk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(229, 231, 235, 1);
        }
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .pulse-dot {
            height: 8px; width: 8px; background-color: #10b981;
            border-radius: 50%; display: inline-block;
            margin-right: 6px; animation: pulse 2s infinite;
        }
        @keyframes pulse { 0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); } 70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); } 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }
    </style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-900">

    <div class="max-w-4xl mx-auto py-10 px-4">
        <!-- Header Section -->
        <div class="mb-8 flex flex-col md:flex-row md:items-start justify-between gap-4 border-b border-gray-200 pb-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="bg-indigo-600 text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-widest">Production</span>
                    <a href="stayora.com.pk" target="_blank" class="group">
                        <h1 class="text-xl font-bold text-gray-600 group-hover:text-indigo-600 transition-colors">
                            stayora<span class="text-indigo-600 group-hover:text-indigo-800">.com.pk</span>
                            <svg class="inline-block w-3 h-3 ml-1 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </h1>
                    </a>
                </div>
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-900">Deploy Dashboard</h2>
                <div class="flex items-center mt-2 text-sm text-gray-500">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.43.372.823 1.102.823 2.222 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
                    <a href="https://github.com/stayoraguesthouse-code/StayOra" target="_blank" class="hover:text-indigo-600 transition-colors">stayoraguesthouse-code/StayOra</a>
                </div>
            </div>
            <div class="flex flex-col items-end gap-2">
                <div class="flex items-center text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">
                    <span class="pulse-dot"></span> Auto-Syncing (Real-time)
                </div>
                <button id="refreshBtn" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition-all shadow-md">
                    <svg id="refreshIcon" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Sync GitHub
                </button>
            </div>
        </div>

        <!-- Controls Section -->
        <div class="glass-card rounded-xl p-6 shadow-sm mb-6 border-l-4 border-l-indigo-500">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Branch Selection -->
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Target Branch</label>
                    <select id="branchSelect" class="block w-full pl-3 pr-10 py-3 text-base border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 rounded-lg border bg-white transition-all">
                        <option value="main-so" selected>main-so</option>
                        <option value="main-copilot">main-copilot</option>
                    </select>
                </div>

                <!-- Commit List -->
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Available Commits</label>
                    <select id="commitSelect" class="block w-full pl-3 pr-10 py-3 text-sm border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 rounded-lg border bg-white transition-all">
                        <option value="" disabled selected>Loading GitHub commits...</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Error Notification -->
        <div id="errorAlert" class="hidden animate-fade-in mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-center justify-between">
            <div class="flex items-center text-red-800">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span id="errorMessage" class="text-sm font-medium">GitHub API سے کنکشن میں مسئلہ ہے۔</span>
            </div>
            <button onclick="fetchGitHubCommits(true)" class="bg-red-600 text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-red-700 transition-colors shadow-sm">
                Retry Now
            </button>
        </div>

        <!-- Notification for New Changes -->
        <div id="newChangesAlert" class="hidden animate-fade-in mb-6 bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center justify-between">
            <div class="flex items-center text-amber-800">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                <span class="text-sm font-medium">New commits detected on GitHub!</span>
            </div>
            <button id="deployNewBtn" class="bg-amber-600 text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-amber-700 transition-colors shadow-sm">
                Deploy Latest
            </button>
        </div>

        <!-- Details Box -->
        <div id="detailsBox" class="hidden glass-card rounded-xl shadow-lg overflow-hidden border-indigo-100 border-2 animate-fade-in">
            <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex justify-between items-center">
                <h2 class="text-sm font-bold text-indigo-800 uppercase tracking-wider">GitHub Commit & Status</h2>
                <span id="detailStatus" class="bg-green-100 text-green-700 text-[10px] px-2 py-1 rounded-full font-bold uppercase tracking-tighter">Verified</span>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Commit Information -->
                    <div>
                        <h3 id="detailTitle" class="text-xl font-bold text-gray-900 mb-3 leading-tight">---</h3>
                        <div class="space-y-4">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Applied Commit ID (SHA)</span>
                                <code id="detailId" class="text-indigo-600 font-mono text-sm mt-1 bg-indigo-50/50 p-2 rounded border border-indigo-100/50 break-all">---</code>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Date & Time (UTC)</span>
                                <span id="detailDate" class="text-gray-900 text-sm font-semibold mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    ---
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Message, Description & Status -->
                    <div class="flex flex-col gap-4">
                        <div class="bg-gray-50 rounded-lg p-5 border border-gray-100 flex-grow">
                            <h4 class="text-[10px] font-bold text-gray-400 uppercase mb-3">Message & Description</h4>
                            <p id="detailDesc" class="text-gray-700 leading-relaxed text-sm whitespace-pre-line font-medium">
                                No description provided.
                            </p>
                        </div>
                        
                        <!-- Deploy Status Result Terminal -->
                        <div class="bg-gray-900 rounded-lg p-4 border border-gray-800 shadow-inner">
                            <h4 class="text-[10px] font-bold text-gray-400 uppercase mb-2 flex items-center">
                                <svg class="w-3 h-3 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M4 17h16a2 2 0 002-2V5a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                Deploy Status Result
                            </h4>
                            <p id="deployStatusResult" class="text-sm font-mono text-emerald-400 break-words">
                                > Synced with GitHub. Ready to Pull & Deploy.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Action Button -->
                <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                    <button id="executeDeployBtn" class="w-full md:w-auto bg-gray-900 text-white px-10 py-3 rounded-lg font-bold hover:bg-black transition-all shadow-xl hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center">
                        <svg id="deployIcon" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                        Pull & Deploy
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-12 pt-8 border-t border-gray-200 text-center">
            <p class="text-[10px] text-gray-400 font-medium tracking-widest uppercase">
                Last updated: 2026-04-23 | Bahalim Group Web Ops | <a href="deploy.php" class="underline hover:text-indigo-500 transition-colors">deploy.php</a>
            </p>
        </footer>
    </div>

    <script>
        const REPO_OWNER = 'stayoraguesthouse-code';
        const REPO_NAME = 'StayOra';
        let currentCommits = [];

        const refreshBtn = document.getElementById('refreshBtn');
        const refreshIcon = document.getElementById('refreshIcon');
        const branchSelect = document.getElementById('branchSelect');
        const commitSelect = document.getElementById('commitSelect');
        const detailsBox = document.getElementById('detailsBox');
        const newChangesAlert = document.getElementById('newChangesAlert');
        const errorAlert = document.getElementById('errorAlert');
        const errorMessage = document.getElementById('errorMessage');
        const deployStatusResult = document.getElementById('deployStatusResult');
        const executeDeployBtn = document.getElementById('executeDeployBtn');

        window.addEventListener('DOMContentLoaded', () => {
            fetchGitHubCommits();
            setInterval(fetchGitHubCommits, 60000);
        });

        async function fetchGitHubCommits(isManual = false) {
            if (isManual) {
                refreshIcon.classList.add('animate-spin');
                refreshBtn.disabled = true;
                errorAlert.classList.add('hidden');
                deployStatusResult.innerHTML = '<span class="text-amber-400">> Syncing with GitHub...</span>';
            }

            try {
                const branch = branchSelect.value;
                const url = `https://api.github.com/repos/${REPO_OWNER}/${REPO_NAME}/commits?sha=${encodeURIComponent(branch)}&per_page=10&t=${Date.now()}`;
                
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/vnd.github.v3+json' }
                });
                
                if (response.status === 404) throw new Error(`برانچ '${branch}' نہیں ملی۔`);
                if (response.status === 403) throw new Error('API کی حد ختم ہوگئی ہے۔ تھوڑی دیر بعد کوشش کریں۔');
                if (!response.ok) throw new Error(`GitHub API Error (${response.status})`);
                
                const commits = await response.json();
                if (!Array.isArray(commits)) throw new Error('ڈیٹا کا فارمیٹ درست نہیں ہے۔');

                if (currentCommits.length > 0 && commits.length > 0 && commits[0].sha !== currentCommits[0].sha) {
                    newChangesAlert.classList.remove('hidden');
                }

                currentCommits = commits;
                updateCommitDropdown(commits);
                errorAlert.classList.add('hidden');

                // If user clicked Sync, automatically display the latest commit and its status
                if (isManual && commits.length > 0) {
                    commitSelect.value = commits[0].sha;
                    displayCommitDetails(commits[0]);
                    deployStatusResult.innerHTML = `<span class="text-emerald-400">> Sync Complete. Found Commit: #${commits[0].sha.substring(0,7)}.<br>> Status: Ready to execute Pull & Deploy.</span>`;
                }
                
            } catch (error) {
                console.error('Fetch Error:', error);
                errorMessage.textContent = error.message;
                errorAlert.classList.remove('hidden');
                commitSelect.innerHTML = '<option value="" disabled selected>ایرور: ڈیٹا لوڈ نہیں ہوسکا</option>';
            } finally {
                if (isManual) {
                    setTimeout(() => {
                        refreshIcon.classList.remove('animate-spin');
                        refreshBtn.disabled = false;
                    }, 800);
                }
            }
        }

        refreshBtn.addEventListener('click', () => fetchGitHubCommits(true));

        function updateCommitDropdown(commits) {
            const previousValue = commitSelect.value;
            commitSelect.innerHTML = '<option value="" disabled selected>Select a GitHub commit</option>';
            
            if (commits.length === 0) {
                commitSelect.innerHTML = '<option value="" disabled selected>اس برانچ پر کوئی کمٹ نہیں ملی</option>';
                return;
            }

            commits.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.sha;
                const dateObj = new Date(item.commit.author.date);
                const dateStr = dateObj.toLocaleString();
                const shortSha = item.sha.substring(0, 7);
                const message = item.commit.message.split('\n')[0]; 
                opt.textContent = `[${dateStr}] #${shortSha} - ${message}`;
                commitSelect.appendChild(opt);
            });

            if (previousValue && commits.some(c => c.sha === previousValue)) {
                commitSelect.value = previousValue;
            }
        }

        function displayCommitDetails(commit) {
            document.getElementById('detailTitle').textContent = commit.commit.message.split('\n')[0];
            document.getElementById('detailId').textContent = commit.sha;
            document.getElementById('detailDate').textContent = new Date(commit.commit.author.date).toLocaleString();
            document.getElementById('detailDesc').textContent = commit.commit.message;
            
            detailsBox.classList.remove('hidden');
            detailsBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        commitSelect.addEventListener('change', (e) => {
            const commit = currentCommits.find(c => c.sha === e.target.value);
            if (commit) {
                deployStatusResult.innerHTML = '<span class="text-blue-400">> Selected older commit. Waiting for action.</span>';
                displayCommitDetails(commit);
            }
        });

        branchSelect.addEventListener('change', () => {
            commitSelect.innerHTML = '<option value="" disabled selected>برانچ تبدیل ہو رہی ہے...</option>';
            detailsBox.classList.add('hidden');
            newChangesAlert.classList.add('hidden');
            fetchGitHubCommits(true);
        });

        // Pull & Deploy Simulation (Updates the Deploy Status Result box)
        executeDeployBtn.addEventListener('click', () => {
            if (currentCommits.length === 0) return;
            
            const btnIcon = document.getElementById('deployIcon');
            executeDeployBtn.disabled = true;
            btnIcon.classList.add('animate-spin');
            
            deployStatusResult.innerHTML = '<span class="text-amber-400">> Executing Deploy Script...<br>> Pulling files to /home/arikgrlc/stayora.com.pk</span>';
            
            // Mock API delay for deployment UI feel
            setTimeout(() => {
                deployStatusResult.innerHTML = `<span class="text-green-400">> ✅ Success! Deployed Commit ID: ${commitSelect.value.substring(0,7)} at ${new Date().toLocaleTimeString()}<br>> Website is now LIVE on stayora</span>`;
                btnIcon.classList.remove('animate-spin');
                executeDeployBtn.disabled = false;
            }, 2000);
        });

        document.getElementById('deployNewBtn').addEventListener('click', () => {
            fetchGitHubCommits(true);
            newChangesAlert.classList.add('hidden');
        });
    </script>
</body>
</html>
