using System;
using System.Net.Http;
using System.Threading.Tasks;

private static readonly HttpClient client = new HttpClient();

public static async Task Run(TimerInfo myTimer, ILogger log)
{
    const string url = "https://stellaruisecretapiappprod.azurewebsites.net/api/v1/secretcontroller/scheduler";
    using (var response = await client.GetAsync(url))
    {
        response.EnsureSuccessStatusCode();
        log.LogInformation("Secret expiration completed through UI API. Status: {StatusCode}", (int)response.StatusCode);
    }
}
