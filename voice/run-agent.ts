import { Agent, CursorAgentError } from "@cursor/sdk";

type Payload = {
    runId: number;
    transcript: string;
    actingUserId: number;
    actingUserName: string;
    cwd: string;
};

const chunks: Buffer[] = [];
for await (const chunk of process.stdin) {
    chunks.push(chunk as Buffer);
}

let payload: Payload;
try {
    payload = JSON.parse(Buffer.concat(chunks).toString("utf8")) as Payload;
} catch {
    process.stderr.write("The voice instruction was unreadable.\n");
    process.stdout.write(JSON.stringify({ status: "error", result: "The voice instruction was unreadable." }));
    process.exit(1);
}

const prompt = [
    `The acting user is ${payload.actingUserName} (id ${payload.actingUserId}).`,
    `The spoken instruction is: ${payload.transcript}`,
    "Do that instruction with the crm tools only.",
    "Look up ids with the list and show tools before a tool that needs an id.",
    "If the instruction matches no tool, do nothing and say it is not an available action.",
].join("\n");

try {
    const result = await Agent.prompt(prompt, {
        apiKey: process.env.CURSOR_API_KEY ?? "",
        model: { id: process.env.CURSOR_MODEL || "composer-2.5" },
        tools: ["mcp"],
        local: {
            cwd: payload.cwd,
            settingSources: [],
        },
        mcpServers: {
            crm: {
                type: "stdio",
                command: process.execPath,
                args: ["--experimental-strip-types", "voice/crm-mcp.ts"],
                cwd: payload.cwd,
                env: {
                    PATH: process.env.PATH ?? "",
                    VOICE_TOKEN: process.env.VOICE_TOKEN ?? "",
                    VOICE_APP_URL: process.env.VOICE_APP_URL ?? "",
                    VOICE_RUN_ID: String(payload.runId),
                },
            },
        },
    });

    process.stdout.write(JSON.stringify({ status: result.status, result: result.result ?? "" }));
    if (result.status !== "finished") {
        process.exit(1);
    }
} catch (error) {
    const message = error instanceof CursorAgentError ? error.message : "The voice agent failed.";
    process.stderr.write(`${message}\n`);
    process.stdout.write(JSON.stringify({ status: "error", result: message }));
    process.exit(1);
}
