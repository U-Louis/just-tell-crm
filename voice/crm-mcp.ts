import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import { CallToolRequestSchema, ListToolsRequestSchema } from "@modelcontextprotocol/sdk/types.js";

type Tool = {
    name: string;
    description: string;
    inputSchema: Record<string, unknown>;
};

const appUrl = (process.env.VOICE_APP_URL ?? "").replace(/\/$/, "");
const headers = {
    Authorization: `Bearer ${process.env.VOICE_TOKEN ?? ""}`,
    "X-Voice-Run": process.env.VOICE_RUN_ID ?? "",
    Accept: "application/json",
};

const catalogResponse = await fetch(`${appUrl}/voice/catalog`, { headers });
if (!catalogResponse.ok) {
    process.stderr.write(`The voice catalog returned ${catalogResponse.status}.\n`);
    process.exit(1);
}

const catalog = (await catalogResponse.json()) as { tools: Tool[] };
const server = new Server({ name: "crm", version: "1.0.0" }, { capabilities: { tools: {} } });

server.setRequestHandler(ListToolsRequestSchema, async () => ({
    tools: catalog.tools.map((tool) => ({
        name: tool.name,
        description: tool.description,
        inputSchema: tool.inputSchema,
    })),
}));

server.setRequestHandler(CallToolRequestSchema, async (request) => {
    const response = await fetch(`${appUrl}/voice/tools/${request.params.name}`, {
        method: "POST",
        headers: {
            ...headers,
            "Content-Type": "application/json",
        },
        body: JSON.stringify(request.params.arguments ?? {}),
    });
    const text = await response.text();

    return {
        content: [{ type: "text", text }],
        isError: !response.ok,
    };
});

await server.connect(new StdioServerTransport());
